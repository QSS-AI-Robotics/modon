$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    function handleUserTypeChange(skipUncheck = false) {
        const selectedText = $('#user_type_id option:selected').text().trim().toLowerCase();
        const isPilot = selectedText === 'pilot';
    
        // Toggle pilot license fields
        $('#pilotFields').toggleClass('d-none', !isPilot);
        if (!isPilot) {
            $('#license_no, #license_expiry').val('');
        }
    
        // Bind checkbox logic
        $('.region-checkbox').off('change').on('change', function () {
            const currentType = $('#user_type_id option:selected').text().trim().toLowerCase();
    
            if (currentType !== 'pilot') {
                $('.region-checkbox').not(this).prop('checked', false);
            }
        });
    
        // Uncheck multiple selections ONLY if not pilot
        if (!isPilot && !skipUncheck) {
            if ($('.region-checkbox:checked').length > 1) {
                $('.region-checkbox').prop('checked', false);
            }
        }
    }
    
    
    
    $('#user_type_id').on('change', function () {
        handleUserTypeChange(false); // allow behavior update
    });
    
    handleUserTypeChange(true);
    
    // on change region start

    function filterLocationsByRegion() {
        const selectedRegions = $('.region-checkbox:checked')
            .map(function () {
                return $(this).next('label').text().trim().toLowerCase();
            })
            .get(); // array of selected region names
    
        $('#location_id option').each(function () {
            const regionAttr = $(this).data('region');
    
            if (!regionAttr) {
                $(this).hide(); // Hide default "Select Location"
                return;
            }
    
            const locationRegions = regionAttr.toLowerCase().split(',');
    
            // Show if there's any match between selected and location regions
            const shouldShow = selectedRegions.some(region => locationRegions.includes(region));
    
            $(this).toggle(shouldShow);
        });
    
        // Reset selection if the currently selected location is now hidden
        const selectedOption = $('#location_id').find('option:selected');
        if (!selectedOption.is(':visible')) {
            $('#location_id').val('');
        }
    }

    

    function handleCityRolesCheck() {
        const userType = $('#user_type_id option:selected').text().trim().toLowerCase();
    
        const selectedRegions = $('.region-checkbox:checked')
            .map(function () {
                return $(this).next('label').text().trim().toLowerCase();
            }).get();
    
        console.log(`User type is: ${userType}`);
        console.log(`Region(s) selected: ${selectedRegions.join(', ')}`);
    
        const isCityRole = userType === 'city supervisor' || userType === 'city manager';
        const isValidRegion = selectedRegions.some(region =>
            ['central', 'eastern', 'western'].includes(region)
        );
    
        if (isCityRole && isValidRegion) {
            filterLocationsByRegion();
            $('#LocationsFields').removeClass('d-none');
        } else {
            $('#LocationsFields').addClass('d-none');
        }
    }
    
    
    
    // Bind it to the dropdown change
    $('#user_type_id').on('change', handleCityRolesCheck);
    $(document).on('change', '.region-checkbox', function () {
      
        handleCityRolesCheck();
    });
    
    

    
    // on change region end

    getAllusers();
    function getAllusers() {
        resetForm();
        $.ajax({
            url: "/dashboard/users",
            type: "GET",
            success: function (response) {
                console.log("Users Response:", response);
                $('#userTableBody').empty();
    
                if (!response.users || response.users.length === 0) {
                    $('#userTableBody').append(`
                        <tr>
                            <td colspan="6" class="text-center text-muted">No users available.</td>
                        </tr>
                    `);
                    return;
                }
    
                $.each(response.users, function (index, user) {
                    let licenseTooltip = '';
                    if (user.user_type?.toLowerCase() === 'pilot' && user.license_no && user.license_expiry) {
                        licenseTooltip = `
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            data-license-no="${user.license_no}"
                            data-license-expiry="${user.license_expiry}"
                            title="<strong class='text-dark'>License No:</strong> ${user.license_no}<br><strong class='text-dark'>Expiry:</strong> ${user.license_expiry}"`
                    }
    
                    let regionTooltip = '';
                    let formattedRegions = 'N/A';
                    let regionRaw = '';
    
                    if (Array.isArray(user.region)) {
                        const formattedList = user.region.map(r =>
                            r.toLowerCase() === 'all'
                                ? 'Headquarter'
                                : r.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
                        );
    
                        formattedRegions = formattedList.join(', ');
                        regionRaw = user.region.join(',');
    
                        regionTooltip = `
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            title="<strong class='text-dark'>Assigned Regions:</strong><br>${formattedList.join('<br>')}"`;
                    } else if (typeof user.region === 'string') {
                        formattedRegions =
                            user.region.toLowerCase() === 'all'
                                ? 'Headquarter'
                                : user.region.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    
                        regionRaw = user.region;
                    }
    
                    // 👉 Location tooltip for city roles
                    let locationTooltip = '';
                    let locationText = '';
                    let locationIds = '';
    
                    if (user.locations && user.locations.length > 0) {
                        const formattedLocations = user.locations.map(loc =>
                            (typeof loc === 'object' && loc.name)
                                ? loc.name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
                                : loc
                        );
    
                        const idList = user.locations.map(loc =>
                            typeof loc === 'object' ? loc.id : null
                        ).filter(Boolean);
    
                        locationText = formattedLocations.join(', ');
                        locationIds = idList.join(',');
    
                        locationTooltip = `
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            title="<strong class='text-dark'>Assigned Locations:</strong><br>${formattedLocations.join('<br>')}"
                            data-location="${locationText}"
                            data-location-id="${locationIds}"`;
                    }
    
                    const row = `
                        <tr data-id="${user.id}">
                            <td>
                                <img src="/storage/users/${user.image}" 
                                     onerror="this.onerror=null;this.src='/images/default-user.png';" 
                                     alt="User" 
                                     class="rounded" 
                                     style="width:24px; height:24px;">
                            </td>
                            <td class="text-capitalize">${user.name}</td>
                            <td>${user.email}</td>
                            <td class="text-capitalize mover pilot-td" ${licenseTooltip} ${locationTooltip}>
                                ${user.user_type
                                    ? user.user_type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
                                    : "N/A"}
                            </td>
                            <td class="text-capitalize" data-regions-name="${regionRaw}" ${regionTooltip}>
                                ${formattedRegions}
                            </td>
                            <td>
                                <img src="../images/edit.png" alt="Edit" class="edit-user img-fluid actions" data-id="${user.id}">
                                <img src="../images/delete.png" alt="Delete" class="delete-user img-fluid actions" data-id="${user.id}">
                            </td>
                        </tr>
                    `;
    
                    $('#userTableBody').append(row);
                });
    
                // ✅ Reinitialize all tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
            },
            error: function (xhr) {
                console.error("❌ Error fetching users:", xhr.responseText);
            }
        });
    }
    
    

    
 
 
    $('#user_image').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on('submit', '#userStoreForm', function (e) {
        e.preventDefault();
    
        const $errorDiv = $('#users-validation-errors').addClass('d-none');
        const formData = new FormData(this);
    
        const name = $('#name').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const user_type_id = $('#user_type_id').val();
        const user_type_text = $('#user_type_id option:selected').text().trim().toLowerCase();
    
        const licenseNo = $('#license_no').val().trim();
        const licenseExpiry = $('#license_expiry').val().trim();
    
        const selectedRegions = $('.region-checkbox:checked').map(function () {
            return this.value;
        }).get();
    
        const selectedLocations = $('#location_id option:selected').val();
    
        const $submitBtn = $(this).find('button[type="submit"]');
        const isUpdate = $submitBtn.text().trim().toLowerCase().includes('update');
    
        // ✅ Base validation
        if (!name || !email || !/^\S+@\S+\.\S+$/.test(email)) {
            $errorDiv.removeClass('d-none').text("Please enter a valid name and email.");
            return;
        }
    
        if (!isUpdate && (!password || password.length < 6)) {
            $errorDiv.removeClass('d-none').text("Password is required (min 6 characters) when creating a user.");
            return;
        }
    
        if (!user_type_id) {
            $errorDiv.removeClass('d-none').text("Please select a user type.");
            return;
        }
    
        if (selectedRegions.length === 0) {
            $errorDiv.removeClass('d-none').text("Please select at least one region.");
            return;
        }
    
        // ✅ City Manager / Supervisor must select location
        if ((user_type_text === 'city manager' || user_type_text === 'city supervisor') && !selectedLocations) {
            $errorDiv.removeClass('d-none').text("Please select a location for this role.");
            return;
        }
    
        // ✅ Pilot-specific validation
        if (user_type_text === 'pilot') {
            if (!licenseNo || !licenseExpiry) {
                $errorDiv.removeClass('d-none').text("License number and expiry date are required for pilots.");
                return;
            }
            formData.append('license_no', licenseNo);
            formData.append('license_expiry', licenseExpiry);
        }
    
        // ✅ Append assigned regions
        selectedRegions.forEach(regionId => {
            formData.append('assigned_regions[]', regionId);
        });
    
        // ✅ Append assigned location (for city roles)

        // ✅ First, ensure no stale location data is present
        formData.delete('location_id');

        // 👇 Only append location_id if user type requires it
        if (user_type_text === 'city manager' || user_type_text === 'city supervisor') {
            const selectedLocations = $('#location_id').val(); // or $('.location-checkbox:checked') if multi
            if (!selectedLocations) {
                $errorDiv.removeClass('d-none').text("Please select a location for this role.");
                return;
            }
            formData.append('location_id', selectedLocations);
        }
    
        // ✅ Submit AJAX
        const url = isUpdate ? `/dashboard/users/${$('#userId').val()}` : '/dashboard/users/storeuser';
        const method = isUpdate ? 'POST' : 'POST';
    
        if (isUpdate) {
            formData.append('_method', 'PUT');
        }
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        $.ajax({
            url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'User saved successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
                const isUpdate = $('#userId').val().trim() !== ''; // If userId is set, it's an edit
                if (!isUpdate) {
                    sendMail();
                }
                $(".cancel-btn").addClass("d-none");
                resetForm();
                getAllusers();
                $errorDiv.addClass('d-none');
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                const errorMessage = response?.message || '';
    
                if (errorMessage.includes('Duplicate entry') && errorMessage.includes('users_email_unique')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Already Exists!',
                        text: 'A user with this email already exists. Please use a different email.',
                    });
                    return;
                }
    
                if (response?.errors) {
                    const messages = Object.values(response.errors).flat();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: `<ul style="text-align:left;">${messages.map(msg => `<li>${msg}</li>`).join('')}</ul>`
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage || 'Something went wrong.',
                    });
                }
            }
        });
    });

    function sendMail() {
        // ✅ Show SweetAlert loading indicator
        Swal.fire({
            title: 'Sending Email...',
            html: 'Please wait while we Send the Email with Credentials.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        const email = $('#email').val().trim();
        const name = $('#name').val().trim();
        const password = $('#password').val().trim();
        const subject = "Your Account Has Been Created";
        const content = `
            Dear ${name},
    
            Your account has been created by the admin. Below are your login details:
    
            Username: ${email}
            Password: ${password}
    
            Please log in and update your password as soon as possible.
    
            Best regards,
            Admin Team
        `;
    
        fetch('/send-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({ recipients: [email], subject, content })
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                text: data.message || 'Welcome email sent successfully.',
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Email Error!',
                text: 'An error occurred while sending the email.',
            });
        });
    }
    
//                 function sendMail(){

//                     // ✅ Show the loader before sending the email
//                     const emailLoader = $('#emailLoader');
//                     emailLoader.removeClass('d-none');
                
//                     // ✅ Send email after user is saved
//                     const email = $('#email').val().trim();
//                     const name = $('#name').val().trim();
//                     const password = $('#password').val().trim();
//                     const subject = "Your Account Has Been Created";
//                     const content = `
//                         Dear ${name},
                
//                         Your account has been created by the admin. Below are your login details:
                
//                         Username: ${email}
//                         Password: ${password}
                
//                         Please log in and update your password as soon as possible.
                
//                         Best regards,
//                         Admin Team
//                     `;
                
//                     // ✅ Add multiple recipients (hardcoded for testing)
//                     //const recipients = [email, "nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
                
//                     fetch('/send-email', {
//                         method: 'POST',
//                         headers: {
//                             'Content-Type': 'application/json',
//                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                         },
//                         body: JSON.stringify({ recipients: [email], subject, content }) // Use only the email from the form
//                     })
//                     .then(response => response.json())
//                     .then(data => {
//                         Swal.fire({
//                             icon: 'success',
//                             title: 'Email Sent!',
//                             text: data.message || 'Welcome email sent successfully.',
//                             timer: 2000,
//                             showConfirmButton: false
//                         });
//                     })
//                     .catch(error => {
//                         console.error('Error:', error);
//                         Swal.fire({
//                             icon: 'error',
//                             title: 'Email Error!',
//                             text: 'An error occurred while sending the email.',
//                         });
//                     })
//                     .finally(() => {
//                         // ✅ Hide the loader after email is sent
//                         emailLoader.addClass('d-none');
//                     });
//                     // formData.append('force_password_reset', true); 
                
//              
//         }
    
    $(document).on('click', '.edit-user', function () {
        $(".cancel-btn").removeClass("d-none");
    
        const row = $(this).closest('tr');
        const userId = $(this).data('id');
    
        const name = row.find('td:eq(1)').text().trim();
        const email = row.find('td:eq(2)').text().trim();
        const userType = row.find('td:eq(3)').text().trim().toLowerCase();
        const imageSrc = row.find('td:eq(0)').find('img').attr('src');
    
        $('#userId').val(userId);
        $('#name').val(name);
        $('#email').val(email);
        $('#password').val('');
    
        // ✅ Reset region checkboxes
        $('.region-checkbox').prop('checked', false);
    
        // ✅ Set assigned regions
        const assignedRegionNames = row.find('td:eq(4)').data('regions-name');
        if (assignedRegionNames) {
            assignedRegionNames.toLowerCase().split(',').forEach(regionName => {
                regionName = regionName.trim();
                $('.region-checkbox').each(function () {
                    const labelText = $(this).next('label').text().trim().toLowerCase();
                    if (
                        (regionName === 'all' && labelText === 'headquarter') ||
                        labelText === regionName
                    ) {
                        $(this).prop('checked', true);
                    }
                });
            });
        }
    
        // ✅ Set user type
        $('#user_type_id option').each(function () {
            if ($(this).text().trim().toLowerCase() === userType) {
                $(this).prop('selected', true);
            }
        });
    
        // ✅ Image preview
        $('#imagePreview').attr('src', imageSrc).removeClass('d-none');
    
        // ✅ Pilot-specific fields
        if (userType === 'pilot') {
            const pilotTd = row.find('.pilot-td');
            const licenseNo = pilotTd.attr('data-license-no') || '';
            const expiry = pilotTd.attr('data-license-expiry') || '';
            $('#license_no').val(licenseNo);
            $('#license_expiry').val(expiry);
            $('#pilotFields').removeClass('d-none');
        } else {
            $('#license_no').val('');
            $('#license_expiry').val('');
            $('#pilotFields').addClass('d-none');
        }
    
        // ✅ City-level location selection
        const locationName = row.find('.pilot-td').data('location');
        const locationId = row.find('.pilot-td').data('location-id');
    
        if ((userType === 'city manager' || userType === 'city supervisor') && locationName && locationId) {
            console.log("✅ City-level user detected.");
            console.log("📍 Location Name:", locationName);
            console.log("🆔 Location ID:", locationId);
    
            $('#LocationsFields').removeClass('d-none');
            filterLocationsByRegion(); // 🔄 Filter before applying
    
            // ✅ Select by exact ID match
            $('#location_id').val(locationId);
    
            // Just for debugging, confirm what was selected
            const selectedText = $('#location_id option:selected').text().trim();
            console.log("🎯 Selected Location:", selectedText);
        } else {
            $('#LocationsFields').addClass('d-none');
            $('#location_id').val('');
        }
    
        // ✅ Update submit button
        $('#userStoreForm button[type="submit"]').text('Update User');
    });
    
// $(document).on('click', '.edit-user', function () {
//     $(".cancel-btn").removeClass("d-none");

//     const row = $(this).closest('tr');
//     const userId = $(this).data('id');

//     const name = row.find('td:eq(1)').text().trim();
//     const email = row.find('td:eq(2)').text().trim();
//     const userType = row.find('td:eq(3)').text().trim().toLowerCase();
//     const imageSrc = row.find('td:eq(0)').find('img').attr('src');

//     $('#userId').val(userId);
//     $('#name').val(name);
//     $('#email').val(email);
//     $('#password').val('');

//     // Reset region checkboxes
//     $('.region-checkbox').prop('checked', false);

//     // ✅ Assigned regions from data attribute
//     const assignedRegionNames = row.find('td:eq(4)').data('regions-name');
//     if (assignedRegionNames) {
//         assignedRegionNames.toLowerCase().split(',').forEach(regionName => {
//             regionName = regionName.trim();
//             $('.region-checkbox').each(function () {
//                 const labelText = $(this).next('label').text().trim().toLowerCase();
//                 if (
//                     (regionName === 'all' && labelText === 'headquarter') ||
//                     labelText === regionName
//                 ) {
//                     $(this).prop('checked', true);
//                 }
//             });
//         });
//     }

//     // Set user type
//     $('#user_type_id option').each(function () {
//         if ($(this).text().trim().toLowerCase() === userType) {
//             $(this).prop('selected', true);
//         }
//     });

//     // Set image preview
//     $('#imagePreview').attr('src', imageSrc).removeClass('d-none');

//     // ✅ Handle pilot-specific fields
//     if (userType === 'pilot') {
//         const pilotTd = row.find('.pilot-td');
//         const licenseNo = pilotTd.attr('data-license-no') || '';
//         const expiry = pilotTd.attr('data-license-expiry') || '';
//         $('#license_no').val(licenseNo);
//         $('#license_expiry').val(expiry);
//         $('#pilotFields').removeClass('d-none');
//     } else {
//         $('#license_no').val('');
//         $('#license_expiry').val('');
//         $('#pilotFields').addClass('d-none');
//     }

//     // ✅ Handle city-level user locations
//     const locationName = row.find('.pilot-td').data('location'); // Gets "Dammam Third Industrial City"

//     if ((userType === 'city manager' || userType === 'city supervisor') && locationName) {
//         console.log("✅ City-level user detected.");
//         console.log("🟨 Retrieved Location Name from row:", locationName);
    
//         $('#LocationsFields').removeClass('d-none');
//         filterLocationsByRegion();
    
//         let found = false;
    
//         $('#location_id option').each(function () {
//             const optionText = $(this).text().trim().toLowerCase();
//             const optionVal = $(this).val();
    
//             if ($(this).is(':visible') && optionText === locationName.toLowerCase()) {
//                 $(this).prop('selected', true);
//                 found = true;
//                 console.log("🎯 Matched and selected location ID:", optionVal);
//                 return false; // break loop
//             }
//         });
    
//         if (!found) {
//             console.warn("⚠️ Location not matched:", locationName);
//         }
    
//     } else {
//         console.log("❌ User is not City Manager/Supervisor or no location found.");
//         $('#LocationsFields').addClass('d-none');
//         $('#location_id').val('');
//     }
    
    
    
    
    

//     // ✅ Update button text
//     $('#userStoreForm button[type="submit"]').text('Update User');
// });

   
    
    

    function resetForm() {

        $('#userStoreForm')[0].reset();
        $('#userId').val('');
        $('#users-validation-errors').addClass('d-none').text('');;
        $('#userStoreForm button[type="submit"]').text('Create User');
        $('#imagePreview').attr('src', '').addClass('d-none'); 
        $('#license_no').val('');
        $('#license_expiry').val('');
        $('#pilotFields').addClass('d-none');
        $('#LocationsFields').addClass('d-none');
        $('#location_id').val('');
    }
    $(document).on("click", ".cancel-btn", function () {
        $(".cancel-btn").addClass("d-none");
        resetForm()
    });


   

    // Delete user
    $(document).on('click', '.delete-user', function () {
        const userId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/dashboard/user/${userId}`,
                    type: "POST",
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'User has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        getAllusers();
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
