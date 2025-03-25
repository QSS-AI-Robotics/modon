$(document).ready(function () {
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
    
    // $(document).on("input", ".search-input", function () {
    //     const searchValue = $(this).val().toLowerCase().trim();
    
    //     // Find the closest table within the same section
    //     const $table = $(this).closest(".search-container").closest(".border-bottom-qss").siblings(".table-responsive").find("table");
    
    //     $table.find("tbody tr").each(function () {
    //         const rowText = $(this).text().toLowerCase();
    //         if (rowText.includes(searchValue)) {
    //             $(this).show();
    //         } else {
    //             $(this).hide();
    //         }
    //     });
    // });
    
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
    



});
