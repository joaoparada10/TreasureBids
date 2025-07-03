async function fetchUnseenNotifications() {
  try {
      const response = await fetch('/notifications/unseen', {
          headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
      });
      const data = await response.json();

      const notificationBadge = document.getElementById('notification-count');

      if (data.unseenCount > 0) {
          notificationBadge.textContent = data.unseenCount;
          notificationBadge.style.display = 'inline'; // Show the badge
      } else {
          notificationBadge.style.display = 'none'; // Hide the badge
      }
  } catch (error) {
      console.error('Error fetching unseen notifications:', error);
  }
}

// Call the function initially and set an interval to refresh periodically
fetchUnseenNotifications();
setInterval(fetchUnseenNotifications, 30000); // Refresh every 30 seconds

document.addEventListener('DOMContentLoaded', () => {
  const notificationButton = document.getElementById('notification-button');
  const notificationList = document.getElementById('notification-list');

  // Listener for fetching and displaying unseen notifications
  if (notificationButton) {
      notificationButton.addEventListener('click', async () => {
          try {
              const response = await fetch('/notifications/unseen-list', {
                  headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  },
              });
              const notifications = await response.json();

              notificationList.innerHTML = ''; // Clear the list

              if (notifications.length > 0) {
                  notifications.forEach(notification => {
                      const listItem = document.createElement('li');
                      listItem.classList.add('dropdown-item');
                      listItem.innerHTML = `
                          <a href="${notification.url}" class="text-decoration-none notification-link" data-id="${notification.id}">
                              ${notification.text}
                          </a>
                      `;
                      notificationList.appendChild(listItem);
                  });
              } else {
                  notificationList.innerHTML = '<li class="dropdown-item text-muted">No new notifications</li>';
              }
          } catch (error) {
              console.error('Error fetching notifications:', error);
          }
      });
  } else {
      console.error('Notification button not found in the DOM.');
  }

  if (notificationList) {
      notificationList.addEventListener('click', async (event) => {
          const notificationLink = event.target.closest('.notification-link');
          if (!notificationLink) return;

          const notificationId = notificationLink.dataset.id;

          try {
              const response = await fetch('/notifications/mark-as-seen', {
                  method: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                      'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({ id: notificationId }),
              });

              const result = await response.json();

              if (result.success) {
                  notificationLink.closest('.dropdown-item').classList.add('seen');
              } else {
                  console.error('Error marking notification as seen:', result.message);
              }
          } catch (error) {
              console.error('Error marking notification as seen:', error);
          }
      });
  } else {
      console.error('Notification list not found in the DOM.');
  }
});

const pusher = new Pusher(PUSHER_APP_KEY, {
  cluster: PUSHER_APP_CLUSTER,
  encrypted: true,
  authEndpoint: '/broadcasting/auth',
  auth: {
      headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      }
  }
});

const userChannel = pusher.subscribe('private-App.Models.Member.' + AUTH_USER_ID);

userChannel.bind('App\\Events\\FollowedAuctionCancelled', function(data) {
    const notificationSection = document.getElementById('notification-section');
    const notification = document.createElement('div');
    notification.classList.add('alert', 'alert-info');
    notification.textContent = data.message;

    notificationSection.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
});

userChannel.bind('App\\Events\\NewBidNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-success');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});


userChannel.bind('App\\Events\\OutbiddedNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-warning');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\AuctionEndingSoonNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-warning');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\AuctionWinnerNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-primary');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\OwnedAuctionEndingNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-info');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\OwnedAuctionEndedNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-warning');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\OwnedAuctionWinnerNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-primary');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\OwnedAuctionCancelledNotification', function(data) {
  const notificationSection = document.getElementById('notification-section');
  const notification = document.createElement('div');
  notification.classList.add('alert', 'alert-danger');
  notification.textContent = data.message;

  notificationSection.appendChild(notification);

  setTimeout(() => {
      notification.remove();
  }, 5000);
});

userChannel.bind('App\\Events\\ParticipatingAuctionEndedNotification', function(data) {
    const notificationSection = document.getElementById('notification-section');
    const notification = document.createElement('div');
    notification.classList.add('alert', 'alert-warning');
    notification.textContent = data.message;
  
    notificationSection.appendChild(notification);
  
    setTimeout(() => {
        notification.remove();
    }, 5000);
  });

