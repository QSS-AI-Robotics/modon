$(document).ready(function () {
    fetchMissions();
    fetchReports();
    // fetch mission
    function fetchMissions() {
        $.ajax({
            url: "/pilot/missions",
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty();
                $('#inspection_id').empty().append('<option value="">Select an Inspection</option>');
    
                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No new missions available.
                            </td>
                        </tr>
                    `);
                    $("#addReportBtn").prop("disabled", true);
                    return;
                }
    
                console.log(response.missions);
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
                    let locations = mission.locations.map(loc => loc.name).join("<br>");
    
                    // Determine button text based on mission status
                    let buttonText = "";
                    let buttonClass = "btn-danger"; // Default class for styling
    
                    if (mission.status === "Pending") {
                        buttonText = "Start";
                        buttonClass = "btn-danger";
                    } else if (mission.status === "In Progress") {
                        buttonText = "Finish";
                        buttonClass = "btn-warning";
                    } else if (mission.status === "Awaiting Report") {
                        buttonText = "Add Report";
                        buttonClass = "btn-primary";
                    } else if (mission.status === "Completed") {
                        buttonText = "Done";
                        buttonClass = "btn-success";
                    }
    
                    let row = `
                        <tr>
                            <td class="inspection_list" data-inspections='${JSON.stringify(mission.inspection_types)}'>${inspectionTypes}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td class="locations_list" data-locations='${JSON.stringify(mission.locations)}'>${locations}</td>
                           
                            <td>${mission.status}</td>
                            <td>
                                <button class="btn ${buttonClass} mission_status" data-id="${mission.id}"> ${buttonText}</button>
                            </td>
                        </tr>
                    `;
    
                    $('#missionTableBody').append(row);
                });
            }
        });
    }
    


    function getRandomDateTime() {
        let now = new Date();
        let randomDays = Math.floor(Math.random() * 10); // Random days within the next 10 days
        let randomHours = Math.floor(Math.random() * 24);
        let randomMinutes = Math.floor(Math.random() * 60);
        
        let randomDate = new Date();
        randomDate.setDate(now.getDate() + randomDays);
        randomDate.setHours(randomHours);
        randomDate.setMinutes(randomMinutes);
        
        return randomDate.toISOString().slice(0, 16);
    }


    // Handle mission status button click
    $(document).on('click', '.mission_status', function () {

    
    
        let button = $(this);
        let missionId = button.data('id');
        let currentText = button.text().trim();
    
        let newStatus, newButtonText, newButtonClass;
    
        if (currentText === "Start") {
            newStatus = "In Progress";
            newButtonText = "Finish";
            newButtonClass = "btn-warning"; // Change color for progress
        } else if (currentText === "Finish") {
            newStatus = "Awaiting Report";
            newButtonText = "Add Report";
            newButtonClass = "btn-primary";
        } else if (currentText === "Add Report") {
            // Open modal and set mission_id
            $("#inspectionLocationGroup .inspection-location-item").not(":first").remove(); // Remove all but the first row
            $('#addReportModal').modal('show');
            $('#mission_id').val(missionId).trigger('change');
        
            // Get relevant inspections for the selected mission
            let inspectionsData = button.closest('tr').find('.inspection_list').data('inspections');
            let locationData = button.closest('tr').find('.locations_list').data('locations');
        
            // Populate inspection dropdown
            if (inspectionsData) {
                $('#inspection_id').empty().append('<option value="">Inspection Type</option>');
        
                $.each(inspectionsData, function (i, inspection) {
                    $('#inspection_id').append(`<option value="${inspection.id}">${inspection.name}</option>`);
                });
            }
        
            // Populate location dropdown (Fixed: Corrected `#locations_id` to `#location_id`)
            if (locationData) {
                $('#location_id').empty().append('<option value="">Select Location</option>');
        
                $.each(locationData, function (i, location) {
                    $('#location_id').append(`<option value="${location.id}">${location.name}</option>`);
                });
            }
        
            return; // Stop further execution, no AJAX request needed
        } else {
            return;
        }
        
    
        // Update mission status via AJAX
        $.ajax({
            url: "/pilot/missions/update-status",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // Include CSRF token
                mission_id: missionId,
                status: newStatus
            },
            success: function () {
                // Update button text & class dynamically
                button.text(newButtonText).removeClass("btn-success btn-warning btn-primary").addClass(newButtonClass);
                fetchMissions(); // Refresh missions list after updating status
            },
            error: function () {
                alert("Failed to update mission status. Please try again.");
            }
        });
    });



    // ✅ Fetch Reports
    function fetchReports() {
        $.ajax({
            url: "/pilot/reports",
            type: "GET",
            success: function (response) {
                $('#reportTableBody').empty();

                if (response.reports.length === 0) {
                    $('#reportTableBody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No reports submitted yet.
                            </td>
                        </tr>
                    `);
                    return;
                }

                $.each(response.reports, function (index, report) {
                    let images = report.images.map(img => `<img src="/${img.image_path}" width="50">`).join(" ");
                    let videoLink = report.video_url ? `<a href="${report.video_url}" target="_blank">Watch</a>` : "N/A";

                    let row = `
                        <tr id="reportRow-${report.id}">
                            <td>${report.mission.id}</td>
                            <td>${report.report_reference}</td>
                            <td>${report.start_datetime}</td>
                            <td>${report.end_datetime}</td>
                            <td>${videoLink}</td>
                            <td class="imgPanel">${images}</td>
                            <td>${report.description || "N/A"}</td>
                            <td>
                                <button class="btn btn-warning edit-report" data-id="${report.id}">Edit</button>
                                <button class="btn btn-danger delete-report" data-id="${report.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                    $('#reportTableBody').append(row);
                });
            }
        });
    }

    // ✅ Handle Edit Report Button Click
    $(document).on('click', '.edit-report', function () {
        let reportId = $(this).data('id');
        let reportRow = $(`#reportRow-${reportId}`);

        // Open modal
        $('#editReportModal').modal('show');

        // Get values from the table
        $('#edit_report_id').val(reportId);
        $('#edit_start_datetime').val(reportRow.find('td:eq(2)').text().trim());
        $('#edit_end_datetime').val(reportRow.find('td:eq(3)').text().trim());
        $('#edit_description').val(reportRow.find('td:eq(6)').text().trim());

        // Get existing images dynamically
        let existingImagesHtml = "";
        reportRow.find('.imgPanel img').each(function () {
            let imgSrc = $(this).attr('src');
            existingImagesHtml += `
                <div class="image-container d-inline-block position-relative me-2">
                    <img src="${imgSrc}" width="50">
                    <button type="button" class="delete-existing-image btn btn-danger btn-sm position-absolute top-0 end-0" data-src="${imgSrc}">&times;</button>
                </div>
            `;
        });

        // Update preview
        $('#editImagePreview').html(existingImagesHtml);
    });

    // ✅ Handle New Image Preview
    let newImagesArray = []; 
    let existingImages = [];



    // ✅ Delete New Image
    $(document).on('click', '.delete-new-image', function () {
        let imageContainer = $(this).closest('.image-container');
        let imageIndex = parseInt(imageContainer.attr('data-index'), 10);

        if (imageIndex >= 0) {
            newImagesArray.splice(imageIndex, 1);
        }

        imageContainer.remove();
        console.log("Updated New Images Array After Deletion:", newImagesArray);
    });

    // ✅ Delete Existing Image
    $(document).on('click', '.delete-existing-image', function () {
        let imageContainer = $(this).closest('.image-container');
        let imgSrc = imageContainer.find('img').attr('src');

        existingImages = existingImages.filter(image => image !== imgSrc);

        imageContainer.remove();
        console.log("Updated Existing Images Array After Deletion:", existingImages);
    });

    // ✅ Handle Report Update
    $('#editReportForm').on('submit', function (e) {
        e.preventDefault();
        let reportId = $('#edit_report_id').val();
        let formData = new FormData(this);

        $('#edit_images').val(''); 

        existingImages = [];
        $('#editImagePreview .image-container img').each(function () {
            let src = $(this).attr('src');
            if (!src.startsWith("data:image")) {
                existingImages.push(src);
            }
        });

        formData.append('existing_images', JSON.stringify(existingImages));

        if (newImagesArray.length > 0) {
            newImagesArray.forEach(file => {
                formData.append('images[]', file);
            });
        }

        console.log("Existing Images:", existingImages);
        console.log("New Images Sent:", newImagesArray);

        $.ajax({
            url: `/pilot/reports/${reportId}/update`,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                fetchMissions();
                fetchReports();
                existingImages = [];
                newImagesArray = [];
                console.log(response.message);
                $('#editReportModal').modal('hide');
               
            },
            error: function (xhr) {
                console.log("Error updating report: " + xhr.responseText);
                existingImages = [];
                newImagesArray = [];
            }
        });
    });

    // ✅ Delete Report
    $(document).on('click', '.delete-report', function () {
        let reportId = $(this).data('id');

        if (!confirm("Are you sure you want to delete this report?")) return;

        console.log("Deleting report ID:", reportId);

        $.ajax({
            url: `/pilot/reports/${reportId}`,
            type: "POST",
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                console.log(response.message);
                fetchMissions();
                fetchReports();
            },
            error: function (xhr) {
                console.log("Error deleting report: " + xhr.responseText);
            }
        });
    });
    // Reset form fields when modal is opened
    $('#addReportModal').on('shown.bs.modal', function () {
        $('#addReportForm')[0].reset(); 
        $('#imagePreview').empty(); 
    });
    // ✅ Add Report
    // $('#addReportForm').on('submit', function (e) {
    //     e.preventDefault();
    //     let formData = new FormData(this);

    //     $.ajax({
    //         url: "/pilot/reports/store",
    //         type: "POST",
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function () {
    //             fetchReports();
    //             console.log('Report submitted successfully!');
    //             $('#addReportModal').modal('hide');
    //             fetchMissions();
    //         }
    //     });
    // });
    $('#addReportForm').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(); // ✅ Don't use `this` to avoid auto-appending
    
        let isValid = true; // Flag to check if form is valid
    
        console.log("🚀 Starting form submission...");
    
        // ✅ Manually append non-file fields (preventing duplicates)
        formData.append("_token", $('input[name="_token"]').val());
        formData.append("mission_id", $('#mission_id').val());
        formData.append("start_datetime", $('#start_datetime').val());
        formData.append("end_datetime", $('#end_datetime').val());
        formData.append("video_url", $('#video_url').val());
        formData.append("description", $('#description').val());
    
        $(".inspection-location-item").each(function (index) {
            let inspectionId = $(this).find(".inspection_id").val();
            let locationId = $(this).find(".location_id").val();
            let inspectionDescription = $(this).find(".inspectiondescrption").val();
            let fileInput = $(this).find("input[type='file']")[0];
            let images = fileInput.files;
    
            console.log(`📌 Processing Incident #${index}`);
            console.log(`Inspection ID: ${inspectionId}, Location ID: ${locationId}, Description: ${inspectionDescription}`);
    
            if (!inspectionId || !locationId) {
                alert(`❌ Please select an Inspection and Location for incident #${index + 1}`);
                isValid = false;
                return false;
            }
    
            formData.append(`inspection_id[${index}]`, inspectionId);
            formData.append(`location_id[${index}]`, locationId);
            formData.append(`inspectiondescrption[${index}]`, inspectionDescription || "");
    
            // ✅ Prevent Duplicate File Entries
            if (images.length > 0) {
                let addedFiles = new Set();
                for (let i = 0; i < images.length; i++) {
                    let image = images[i];
    
                    if (!addedFiles.has(image.name)) {
                        formData.append(`images_${index}[]`, image);
                        addedFiles.add(image.name);
                        console.log(`📸 Added Image: ${image.name}`);
                    } else {
                        console.warn(`⚠ Skipping duplicate image: ${image.name}`);
                    }
                }
            } else {
                console.warn(`⚠ No images selected for Incident #${index + 1}`);
            }
        });
    
        if (!isValid) {
            console.error("❌ Form submission blocked due to missing values.");
            return;
        }
    
        // 🚀 Log FormData Before Sending
        console.log("🚀 Final FormData before submission:");
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}:`, pair[1]);
        }
    
        $.ajax({
            url: "/pilot/reports/store",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log('✅ Report submitted successfully!', response);
                fetchReports();
                $('#addReportModal').modal('hide');
                fetchMissions();
            },
            error: function (xhr) {
                console.error("❌ Error Response:", xhr.responseText);
                alert("Error submitting report. Please check your inputs.");
            }
        });
    });
    
    
    
    
    
    
    
    let groupIndex = 1; // Unique index for naming input fields dynamically

    // Function to add a new Incident Detail row dynamically
    $(document).on("click", ".addInspectionRow", function () {
        let newGroup = `
            <div class="row mb-3 inspection-location-item">
                <label class="form-label">Incident Detail</label>
                <div class="col-md-3">
                    <select class="form-select inspection_id" name="inspection_id[]" id="inspection_id"  required></select>
                </div>
                <div class="col-md-3">
                    <select class="form-select location_id" name="location_id[]" id="location_id"  required></select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control inspectiondescrption" name="inspectiondescrption[]" placeholder="Inspection Description">
                </div>
                <div class="col-md-2">
                    <input type="file" class="form-control images" name="images_${groupIndex}[]" multiple accept="image/*">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-success addInspectionRow me-2">+</button>
                    <button type="button" class="btn btn-danger removeInspectionRow">-</button>
                </div>
            </div>
        `;

        $("#inspectionLocationGroup").append(newGroup);
        updateDropdowns(); // Populate dropdowns with existing values
        groupIndex++;
    });

    // Function to remove an Incident Detail row
    $(document).on("click", ".removeInspectionRow", function () {
        if ($(".inspection-location-item").length > 1) {
            $(this).closest(".inspection-location-item").remove();
        } else {
            alert("At least one Incident Detail is required.");
        }
    });

    // Function to populate dropdowns with available options
    function updateDropdowns() {
        let inspections = $(".inspection_id:first").html(); // Get options from first dropdown
        let locations = $(".location_id:first").html(); // Get options from first dropdown

        $(".inspection_id").each(function () {
            if ($(this).children().length === 0) {
                $(this).html(inspections);
            }
        });

        $(".location_id").each(function () {
            if ($(this).children().length === 0) {
                $(this).html(locations);
            }
        });
    }

    // Ensure dropdowns are populated on page load
    updateDropdowns();

});
