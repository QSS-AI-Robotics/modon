$(document).ready(function () {
    localStorage.setItem("selectedLang", "ar");
    // Logout AJAX Request
    $('#logoutButton').on('click', function () {
        $.ajax({
            url: "/logout",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                window.location.href = response.redirect; // Redirect to signin page
            }
        });
    });
    $(".search-icon").click(function () {
        $(".search-input").toggleClass("active").show().focus();
    });
    $(".datePanel-icon").click(function () {
        $(".date-fields-wrapper").toggleClass("active");
    });
    
    

    
    $(document).on('input', '.search-input', function () {
        const searchTerm = $(this).val().toLowerCase();
        const $table = $(this).closest('.bg-section').find('table');
        const $rows = $table.find('tbody tr');
        let matchFound = false;
    
        $rows.each(function () {
            const rowText = $(this).text().toLowerCase();
            const isMatch = rowText.includes(searchTerm);
            $(this).toggle(isMatch);
            if (isMatch) matchFound = true;
        });
    
        // Remove any existing "no match" row
        $table.find('tbody .no-record').remove();
    
        // If no match found, show fallback row
        if (!matchFound) {
            $table.find('tbody').append(`
                <tr class="no-record text-center text-muted">
                    <td colspan="${$table.find('thead th').length}">No matching records found.</td>
                </tr>
            `);
        }
    });
    
        // const currentUrl = window.location.pathname;
        //hi
        // $('.nav-link-btn').each(function () {
        //     const linkUrl = $(this).attr('href');

        //     if (currentUrl === linkUrl || currentUrl.startsWith(linkUrl + '/')) {
        //         $(this).addClass('selected');
        //     }
        // });
        setTimeout(function () {
            const getuserValue = $("#passwordResetEnable").val();
            
            if (getuserValue == 1) {
                const modalElement = $("#passwordResetModal");
                modalElement.modal({
                    backdrop: 'static',
                    keyboard: false
                });
                modalElement.modal('show');
            }
        }, 2000);
    
});
