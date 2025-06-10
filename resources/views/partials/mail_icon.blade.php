<div class="relative inline-block text-left ml-4" x-data="{ open: false, activeTab: 'general' }" @click.away="open = false">
    <button type="button" @click="open = !open; if (open) fetchInquiries();" class="inline-flex justify-center w-full rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
        <i class="fas fa-envelope text-xl text-gray-600"></i>
        <span id="unread-inquiries-count" class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" style="display: none;">0</span>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
         role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        <div class="py-1" role="none">
            {{-- Tab Buttons --}}
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'general'"
                        :class="{ 'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'general', 'text-gray-500 hover:text-gray-700': activeTab !== 'general' }"
                        class="flex-1 py-2 text-sm font-medium text-center focus:outline-none hover:bg-gray-50">
                    General (<span id="general-inquiries-count">0</span>)
                </button>
                <button @click="activeTab = 'email'"
                        :class="{ 'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'email', 'text-gray-500 hover:text-gray-700': activeTab !== 'email' }"
                        class="flex-1 py-2 text-sm font-medium text-center focus:outline-none hover:bg-gray-50">
                    Email (<span id="email-inquiries-count">0</span>)
                </button>
            </div>

            <div class="px-4 py-2 text-sm font-semibold border-b border-gray-200">
                Messages
                <span id="inquiry-spinner" class="ml-2 text-gray-500 hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </div>
            
            {{-- Tab Content (General Inquiries) --}}
            <div x-show="activeTab === 'general'" id="general-inquiries-list-container" class="max-h-60 overflow-y-auto">
                <p class="text-center text-gray-500 py-4" id="no-general-inquiries-message">No unread general messages.</p>
            </div>

            {{-- Tab Content (Email Inquiries) --}}
            <div x-show="activeTab === 'email'" id="email-inquiries-list-container" class="max-h-60 overflow-y-auto">
                <p class="text-center text-gray-500 py-4" id="no-email-inquiries-message">No unread email messages.</p>
            </div>

            <div class="border-t border-gray-200 py-1" role="none">
                <button type="button" id="mark-all-inquiries-as-read-btn" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem" tabindex="-1">Mark all as read</button>
                <a href="{{ route('admin.inquiries.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem" tabindex="-1">View all messages</a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const unreadInquiriesCountSpan = document.getElementById('unread-inquiries-count');
        const generalInquiriesListContainer = document.getElementById('general-inquiries-list-container');
        const emailInquiriesListContainer = document.getElementById('email-inquiries-list-container');
        const markAllInquiriesAsReadBtn = document.getElementById('mark-all-inquiries-as-read-btn');
        const noGeneralInquiriesMessage = document.getElementById('no-general-inquiries-message');
        const noEmailInquiriesMessage = document.getElementById('no-email-inquiries-message');
        const inquirySpinner = document.getElementById('inquiry-spinner');

        const generalCountSpan = document.getElementById('general-inquiries-count');
        const emailCountSpan = document.getElementById('email-inquiries-count');

        let allUnreadInquiries = [];

        async function fetchInquiries() {
            inquirySpinner.classList.remove('hidden');
            try {
                const response = await fetch('{{ route('notifications.unread') }}');
                if (!response.ok) {
                    throw new Error('Failed to fetch notifications');
                }
                const data = await response.json();
                
                // =================================================================
                // KEY CORRECTION 1: The filtering logic is fixed here.
                // Instead of filtering by a restrictive `notif.type`, we now filter by `notif.data.source_type`.
                // This ensures that any notification meant for either tab is included.
                // =================================================================
                allUnreadInquiries = data.notifications.filter(notif => 
                        notif.type === 'new_inquiry' ||        // For general customer inquiries
                        notif.type === 'vendor_application' || // For vendor application notifications
                        notif.type === 'tier_upgrade_request'  // For tier upgrade request notifications
                    );

                unreadInquiriesCountSpan.textContent = allUnreadInquiries.length;
                unreadInquiriesCountSpan.style.display = allUnreadInquiries.length > 0 ? 'inline' : 'none';

                renderTabContent();

            } catch (error) {
                console.error('Error fetching inquiries:', error);
            } finally {
                inquirySpinner.classList.add('hidden');
            }
        }

        function renderTabContent() {
            // Filter inquiries for each specific tab
            const generalInquiries = allUnreadInquiries.filter(notif => notif.data.source_type === 'general');
            const emailInquiries = allUnreadInquiries.filter(notif => notif.data.source_type === 'email');

            // Update tab counts
            generalCountSpan.textContent = generalInquiries.length;
            emailCountSpan.textContent = emailInquiries.length;

            // Render content into both containers. Alpine.js's x-show will handle which one is visible.
            renderInquiriesList(generalInquiriesListContainer, generalInquiries, noGeneralInquiriesMessage);
            renderInquiriesList(emailInquiriesListContainer, emailInquiries, noEmailInquiriesMessage);
        }

        function renderInquiriesList(listElement, inquiries, noMessagesElement) {
            listElement.innerHTML = ''; // Clear existing list
            if (inquiries.length > 0) {
                noMessagesElement.style.display = 'none'; // Hide the 'no messages' text
                inquiries.forEach(inquiry => {
                    const inquiryItem = document.createElement('a');
                    inquiryItem.href = inquiry.data.url; // Use `inquiry.data.url` which is more standard for Laravel notifications
                    inquiryItem.classList.add('flex', 'items-start', 'px-4', 'py-2', 'text-sm', 'text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
                    
                    let iconHtml = '';
                    if (inquiry.data.icon) {
                        iconHtml = `<i class="fas ${inquiry.data.icon} mt-1 mr-3 text-lg text-gray-500"></i>`;
                    } else {
                        // Default icon if not provided
                        iconHtml = `<i class="fas fa-envelope mt-1 mr-3 text-lg text-gray-500"></i>`;
                    }

                    inquiryItem.innerHTML = `
                        ${iconHtml}
                        <div class="flex-1">
                            <p class="font-medium">${inquiry.data.message}</p>
                            <p class="text-xs text-gray-500">${inquiry.data.created_at_for_humans}</p>
                        </div>
                        <button type="button" class="mark-inquiry-as-read-btn ml-2 px-2 py-1 rounded-full text-xs text-blue-600 hover:bg-blue-100" data-notification-id="${inquiry.id}" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </button>
                    `;
                    listElement.appendChild(inquiryItem);
                });
            } else {
                noMessagesElement.style.display = 'block'; // Show the 'no messages' text
                // Ensure the placeholder is actually inside the listElement if it's cleared
                listElement.appendChild(noMessagesElement);
            }
        }

        document.querySelector('[x-data]').addEventListener('click', async function (event) {
            if (event.target.closest('.mark-inquiry-as-read-btn')) {
                event.preventDefault();
                event.stopPropagation();
                const button = event.target.closest('.mark-inquiry-as-read-btn');
                const notificationId = button.dataset.notificationId;

                try {
                    const response = await fetch(`{{ url('admin/notifications/mark-as-read') }}/${notificationId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });
                    if (!response.ok) throw new Error('Failed to mark inquiry as read');
                    
                    // Re-fetch inquiries to update the UI
                    fetchInquiries();
                } catch (error) {
                    console.error('Error marking inquiry as read:', error);
                }
            }
        });

        markAllInquiriesAsReadBtn.addEventListener('click', async function () {
            try {
                const response = await fetch('{{ route('notifications.mark_all_as_read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                if (!response.ok) throw new Error('Failed to mark all as read');
                fetchInquiries();
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        });

        // Initial fetch on page load to set the correct initial count
        fetchInquiries();

        // Periodically fetch inquiries to keep the list up-to-date
        setInterval(fetchInquiries, 30000); // 30 seconds
    });
    </script>
    @endpush
</div>