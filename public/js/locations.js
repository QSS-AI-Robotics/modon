$(document).ready(function () {

    function toLangKey(str) {
        return str
            .trim()
            .replace(/\b3\b/g, 'three')      // Replace standalone digit 3 with 'three'
            .replace(/3/g, 'three')          // Replace any 3 with 'three'
            .replace(/&/g, 'and')            // Replace & with and
            .replace(/\s+/g, '_');           // Replace spaces with underscores
    }

    function formatCityNames(text) {
        return text.trim().replace(/\s+/g, '_');
    }
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getLocations()
    function getLocations({ status = null,  page = 1 } = {}) {
        resetForm();
        $.ajax({
            url: "/get-locations", // Route to fetch locations
            type: "GET",
            data: { page },
            success: function (response) {
                console.log(response);
                $('#locationTableBody').empty(); // Clear previous data
    
                if (!response.data || response.data.length === 0) {
                    $('#locationTableBody').append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted">No locations available.</td>
                        </tr>
                    `);
                    return;
                }
                const offset = (response.current_page - 1) * response.per_page;
                // ✅ Loop through locations and append to table
                $.each(response.data, function (index, location) {
                    let regionDisplay = location.region 
                        ? location.region.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) 
                        : "N/A";
    
                    let row = `
                        <tr data-id="${location.id}">
                           <td>${offset + index + 1}</td>
                            <td data-lang-key="${formatCityNames(location.name)}">${location.name}</td>

                            <td class="text-capitalize" data-lang-key="${regionDisplay.toLowerCase()}">${regionDisplay}</td>
                            <td>${location.map_url ? `<a href="${location.map_url}" target="_blank"><span data-lang-key="view">View</span></a>` : 'N/A'}</td>
                            <td>${location.description || 'N/A'}</td>
                            <td>
                                <img src="./images/edit.png" alt="Edit" class="edit-location img-fluid actions" data-id="${location.id}">
                                <img src="./images/delete.png" alt="Delete" class="delete-location img-fluid actions" data-id="${location.id}">
                            </td>
                        </tr>
                    `;
                    $('#locationTableBody').append(row);
                });
                renderMissionPagination(response);
                let currentLang = localStorage.getItem("selectedLang") || "ar";
            //console.log('Calling updateLanguageTexts with:', currentLang);
            updateLanguageTexts(currentLang);
                
            },
            error: function (xhr) {
                console.error("❌ Error fetching locations:", xhr.responseText);
            }
        });
    }
    function renderMissionPagination(response) {
        const paginationWrapper = $('#paginationWrapper');
        paginationWrapper.empty();
    
        const currentPage = response.current_page;
        const lastPage = response.last_page;
    
        if (lastPage <= 1) return; // No pagination needed
    
        let paginationHTML = `<nav><ul class="pagination justify-content-center">`;
    
        // Previous Button
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" data-lang-key="previous">Previous</a>
            </li>`;
    
        // Page numbers (optional: simplify with only nearby pages)
        for (let i = 1; i <= lastPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
        }
    
        // Next Button
        paginationHTML += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" data-lang-key="next">Next</a>
            </li>`;
    
        paginationHTML += `</ul></nav>`;
        paginationWrapper.html(paginationHTML);
    
        // Attach click event
        $('.page-link').on('click', function (e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                getLocations({ page });
            }
        });
    }


    // Open Edit Modal
    $(document).on('click', '.edit-location', function () {
        const row = $(this).closest('tr');
        const locationId = $(this).data('id');
    
        $(".cancel-btn").removeClass("d-none");
    
        // Get language
        let currentLang = localStorage.getItem("selectedLang") || "ar";
        if (currentLang === "ar") {
            $(".form-title").text("تحديث الموقع");
            $(".mission-btn span").text("تحديث الموقع");
        } else {
            $(".form-title").text("Update Location");
            $(".mission-btn span").text("Update Location");
        }
    
        const name = row.find('td:eq(1)').text().trim();
        const regionName = row.find('td:eq(2)').text().trim();
        const mapUrl = row.find('td:eq(3)').find('a').attr('href') || '';
        const description = row.find('td:eq(4)').text().trim();
    
        // Fill form fields
        $('#locationId').val(locationId);
        $('#name').val(name);
        $('#map_url').val(mapUrl);
        $('#description').val(description);
    
        // Match the region in the dropdown
        $('#region_id option').each(function () {
            const optionText = $(this).text().trim().toLowerCase();
            if (optionText === regionName.toLowerCase()) {
                $(this).prop('selected', true);
            }
        });
    
        // Optional: scroll to form
        $('html, body').animate({
            scrollTop: $('#locationForm').offset().top - 100
        }, 300);
    
        $('#name').focus();
    });
    
    
  

    function resetForm(){
        $('#location-validation-errors').addClass('d-none').text('');
        $("#locationForm")[0].reset(); // Reset Form Fields
        $("#locationForm").removeAttr("data-location-id"); // Remove Edit Mode
    
        // Get language
        let currentLang = localStorage.getItem("selectedLang") || "ar";
        if (currentLang === "ar") {
            $(".form-title").text("إنشاء موقع جديد");
            $(".mission-btn span").text("إنشاء الموقع");
        } else {
            $(".form-title").text("Create New Location");
            $(".mission-btn span").text("Create Location");
        }
    
        $(".cancel-btn").addClass("d-none"); // Hide Cancel Button
    }
    $(document).on("click", ".cancel-btn", function () {
        resetForm()
    });


    // Submit Form (Create)
    $('#locationForm').on('submit', function (e) {
        e.preventDefault();
    
        const $errorDiv = $('#location-validation-errors');
        $errorDiv.addClass('d-none').text(''); // Clear previous
    
        const formData = {
            name: $('#name').val().trim(),

            map_url: $('#map_url').val().trim(),
            description: $('#description').val().trim(),
            region_id: $('#region_id').val() // ✅ Get selected region
        };
    
        // Check if any field is empty (excluding optional map_url & description)
        const requiredFields = ['name', 'region_id'];
        const hasEmpty = requiredFields.some(field => !formData[field]);
    
        if (hasEmpty) {
            $errorDiv.removeClass('d-none').text('All required fields must be filled.');
            return;
        }
    
        const locationId = $('#locationId').val();
        const url = locationId ? `/locations/${locationId}/update` : `/locations/store`;
    
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Location saved.',
                    timer: 2000,
                    showConfirmButton: false
                });
                getLocations();
                resetForm(); // Optional: reset form fields
                $errorDiv.addClass('d-none').text('');
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Something went wrong.',
                });
            }
        });
    });
    

    
    


    // Delete Location
    $(document).on('click', '.delete-location', function () {
        let locationId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This location will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/locations/${locationId}`,
                    type: "DELETE",
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Location has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        getLocations();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.',
                        });
                    }
                });
            }
        });
    });
    
});
