<div
    x-data="mailIconComponent()"
    x-init="init()"
    class="relative inline-block text-left ml-4"
    @click.away="open = false"
>
    <button @click="toggle" type="button" class="inline-flex justify-center w-full rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
        <i class="fas fa-envelope text-xl text-gray-600"></i>
        <span x-show="count > 0" x-text="count" class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"></span>
    </button>

    <div
        x-show="open"
        x-transition
        class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
    >
        <div class="py-1" role="none">
            {{-- Tab Buttons --}}
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'direct_messages'"
                        :class="{ 'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'direct_messages', 'text-gray-500 hover:text-gray-700': activeTab !== 'direct_messages' }"
                        class="flex-1 py-2 text-sm font-medium text-center focus:outline-none hover:bg-gray-50">
                    Messages (<span x-text="directMessagesList.length"></span>)
                </button>
                <button @click="activeTab = 'applications'"
                        :class="{ 'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'applications', 'text-gray-500 hover:text-gray-700': activeTab !== 'applications' }"
                        class="flex-1 py-2 text-sm font-medium text-center focus:outline-none hover:bg-gray-50">
                    Applications (<span x-text="applicationsList.length"></span>)
                </button>
            </div>

            <div class="px-4 py-2 text-sm font-semibold border-b border-gray-200">
                Messages
                <span x-show="isLoading" class="ml-2 text-gray-500"><i class="fas fa-spinner fa-spin"></i></span>
            </div>

            {{-- Direct Messages List --}}
            <div x-show="activeTab === 'direct_messages'" class="max-h-60 overflow-y-auto">
                <template x-if="directMessagesList.length === 0">
                    <p class="text-center text-gray-500 py-4">No unread messages.</p>
                </template>
                <template x-for="inquiry in directMessagesList" :key="inquiry.notification_id">
                    <a :href="inquiry.url" class="flex items-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i :class="`fas ${inquiry.icon || 'fa-info-circle'} mt-1 mr-3 text-lg text-gray-500`"></i>
                        <div class="flex-1">
                            <p class="font-medium" x-text="inquiry.message"></p>
                            <p class="text-xs text-gray-500" x-text="inquiry.created_at_for_humans"></p>
                        </div>
                        <button @click.prevent.stop="markAsRead(inquiry.notification_id)" type="button" class="ml-2 px-2 py-1 rounded-full text-xs text-blue-600 hover:bg-blue-100" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </button>
                    </a>
                </template>
            </div>

            {{-- Applications List --}}
            <div x-show="activeTab === 'applications'" class="max-h-60 overflow-y-auto">
                <template x-if="applicationsList.length === 0">
                    <p class="text-center text-gray-500 py-4">No unread applications.</p>
                </template>
                 <template x-for="inquiry in applicationsList" :key="inquiry.notification_id">
                    <a :href="inquiry.url" class="flex items-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i :class="`fas ${inquiry.icon || 'fa-info-circle'} mt-1 mr-3 text-lg text-gray-500`"></i>
                        <div class="flex-1">
                            <p class="font-medium" x-text="inquiry.message"></p>
                            <p class="text-xs text-gray-500" x-text="inquiry.created_at_for_humans"></p>
                        </div>
                        <button @click.prevent.stop="markAsRead(inquiry.notification_id)" type="button" class="ml-2 px-2 py-1 rounded-full text-xs text-blue-600 hover:bg-blue-100" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </button>
                    </a>
                </template>
            </div>

            <div class="border-t border-gray-200 py-1" role="none">
                <button @click="markAllAsRead" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">Mark all as read</button>
                <a href="{{ route('admin.inquiries.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">View all messages</a>
            </div>
        </div>
</div>

@push('scripts')
<script>
    function mailIconComponent() {
        return {
            open: false,
            isLoading: false,
            activeTab: 'direct_messages',
            count: 0,
            allInquiries: [],
            
            get directMessagesList() {
                return this.allInquiries.filter(notif =>
                    notif.source_type === 'general' || (notif.source_type === 'unknown' && notif.type === 'new_inquiry')
                );
            },
            get applicationsList() {
                return this.allInquiries.filter(notif =>
                    notif.source_type === 'application' ||
                    notif.source_type === 'upgrade_request'
                );
            },

            // Methods
            init() {
                this.fetchInquiries();
                setInterval(() => this.fetchInquiries(), 30000);
            },
            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.fetchInquiries();
                }
            },
            async fetchInquiries() {
                this.isLoading = true;
                try {
                    const response = await fetch('{{ route('notifications.unread') }}');
                    const data = await response.json();
                    const filtered = data.notifications.filter(notif =>
                        notif.type === 'new_inquiry' ||
                        notif.type === 'vendor_application' ||
                        notif.type === 'tier_upgrade_request'
                    );
                    this.allInquiries = filtered;
                    this.count = filtered.length;
                } catch (error) {
                    console.error('Error fetching inquiries:', error);
                } finally {
                    this.isLoading = false;
                }
            },
            async markAsRead(notificationId) {
                this.allInquiries = this.allInquiries.filter(i => i.notification_id !== notificationId);
                this.count = this.allInquiries.length;
                
                await fetch(`{{ route('notifications.mark_as_read', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                });
            },
            async markAllAsRead() {
                this.allInquiries = [];
                this.count = 0;

                await fetch('{{ route('notifications.mark_all_as_read') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                });
            }
        }
    }
</script>
@endpush
</div>