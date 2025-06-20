<div class="relative inline-block text-left" x-data="{ open: false }" @click.away="open = false">
    <button type="button" @click="open = !open; if (open) fetchNotifications();" class="inline-flex justify-center w-full rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
        <i class="fas fa-bell text-xl text-gray-600"></i>
        <span id="unread-notifications-count" class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" style="display: none;">0</span>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
         role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        <div class="py-1" role="none">
            <div class="px-4 py-2 text-sm font-semibold border-b border-gray-200">
                Notifications
                <span id="notification-spinner" class="ml-2 text-gray-500 hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </div>
            <div id="notifications-list" class="max-h-60 overflow-y-auto">
                <p class="text-center text-gray-500 py-4" id="no-notifications-message">No unread notifications.</p>
            </div>
            <div class="border-t border-gray-200 py-1" role="none">
                <button type="button" id="mark-all-as-read-btn" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem" tabindex="-1">Mark all as read</button>
                <a href="{{ route('dashboard.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem" tabindex="-1">View all in dashboard</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const unreadCountSpan = document.getElementById('unread-notifications-count');
        const notificationsList = document.getElementById('notifications-list');
        const markAllAsReadBtn = document.getElementById('mark-all-as-read-btn');
        const noNotificationsMessage = document.getElementById('no-notifications-message');
        const notificationSpinner = document.getElementById('notification-spinner');
        const notificationBell = document.querySelector('.fa-bell');

        async function fetchNotifications() {
            notificationSpinner.classList.remove('hidden');
            try {
                const response = await fetch('{{ route('notifications.unread') }}');
                if (!response.ok) {
                    throw new Error('Failed to fetch notifications');
                }
                const data = await response.json();
                
                updateNotificationUI(data.count, data.notifications);
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                notificationSpinner.classList.add('hidden');
            }
        }

        function updateNotificationUI(count, notifications) {
            if (count > 0) {
                unreadCountSpan.textContent = count;
                unreadCountSpan.style.display = 'inline';
                noNotificationsMessage.classList.add('hidden');
            } else {
                unreadCountSpan.style.display = 'none';
                noNotificationsMessage.classList.remove('hidden');
            }

            notificationsList.innerHTML = '';
            if (notifications.length > 0) {
                notifications.forEach(notif => {
                    const notifItem = document.createElement('a');
                    notifItem.href = notif.url;
                    notifItem.classList.add('flex', 'items-start', 'px-4', 'py-2', 'text-sm', 'text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
                    
                    let iconHtml = '';
                    if (notif.icon) {
                        iconHtml = `<i class="fas ${notif.icon} mt-1 mr-3 text-lg text-gray-500"></i>`;
                    } else {
                        iconHtml = `<i class="fas fa-info-circle mt-1 mr-3 text-lg text-gray-500"></i>`;
                    }

                    notifItem.innerHTML = `
                        ${iconHtml}
                        <div class="flex-1">
                            <p class="font-medium">${notif.message}</p>
                            <p class="text-xs text-gray-500">${notif.created_at_for_humans}</p>
                        </div>
                        <button type="button" class="mark-as-read-btn ml-2 px-2 py-1 rounded-full text-xs text-blue-600 hover:bg-blue-100" data-notification-id="${notif.id}" title="Mark as Read"> {{-- Correct: notif.id --}}
                            <i class="fas fa-check"></i>
                        </button>
                    `;
                    notificationsList.appendChild(notifItem);
                });
            }
        }

        if (notificationsList) {
            notificationsList.addEventListener('click', async function (event) {
                if (event.target.closest('.mark-as-read-btn')) {
                    event.preventDefault();
                    event.stopPropagation();
                    const button = event.target.closest('.mark-as-read-btn');
                    const notificationId = button.dataset.notificationId;

                    try {
                        const response = await fetch(`{{ route('notifications.mark_as_read', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            }
                        });
                        if (!response.ok) throw new Error('Failed to mark as read');
                        
                        button.closest('a').remove();
                        fetchNotifications();
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                }
            });
        }

        if (markAllAsReadBtn) {
            markAllAsReadBtn.addEventListener('click', async function () {
                try {
                    const response = await fetch('{{ route('notifications.mark_all_as_read') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });
                    if (!response.ok) throw new Error('Failed to mark all as read');

                    fetchNotifications();
                } catch (error) {
                    console.error('Error marking all notifications as read:', error);
                }
            });
        }

        fetchNotifications();

        // Periodically fetch notifications (e.g., every 30 seconds)
        setInterval(fetchNotifications, 30000);
    });
</script>
@endpush