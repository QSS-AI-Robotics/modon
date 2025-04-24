$(document).ready(function () {
   
    function loadNotifications() {
        fetch('/notifications')  // <- must be routed to fetchUserNotifications controller method
            .then(res => res.json())
            .then(data => {
                console.log("notification-data",data);
                const feed = document.getElementById('notificationFeed');
                const countSpan = document.querySelector('.notification-count');
    
                feed.innerHTML = '';
                countSpan.classList.add('d-none');
    
                if (!data.length) {
                    feed.innerHTML = '<p class="text-muted text-center">No notifications</p>';
                    return;
                }
    
                countSpan.textContent = data.length;
                countSpan.classList.remove('d-none');
    
                data.forEach(notification => {
                    feed.innerHTML += `
                        <div class="notification-item mb-2 p-2  ">
                            <strong>${notification.title}</strong>
                            <p class="mb-1">${notification.message}</p>
                            <small style="color:#999">${new Date(notification.created_at).toLocaleString()}</small>
                        </div>
                    `;
                });
            });
    }
    
  
        
        // loadNotifications();
        // setInterval(loadNotifications, 3000); 

    $('#notificationToggle').on('click', function () {
        $('#notificationDropdown').toggleClass('active');
        $('#profileDropdown').removeClass('active');
    });
});