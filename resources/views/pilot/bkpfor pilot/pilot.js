$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    fetchMissions();
    // fetchReports();
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
    
                // console.log(response.missions);
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
                    let locations = mission.locations.map(loc => loc.name).join("<br>");
                
                    // Determine button text and image source based on mission status
                    let buttonText = "";
                    let buttonClass = "btn-danger";
                    let ImgClass = "bg-danger";
                    let imageSrc = "/images/start.png"; // Default
                
                    if (mission.status === "Pending") {
                        buttonText = "Start";
                        buttonClass = "btn-danger";
                        ImgClass = "bg-danger";
                        imageSrc = "/images/start.png";
                    } else if (mission.status === "In Progress") {
                        buttonText = "Finish";
                        buttonClass = "btn-warning";
                        ImgClass = "bg-warning";
                        imageSrc = "/images/finish.png";
                    } else if (mission.status === "Awaiting Report") {
                        buttonText = "Add Report";
                        buttonClass = "btn-primary";
                        ImgClass = "bg-primary";
                        imageSrc = "/images/uploadreport.png";
                    } else if (mission.status === "Completed") {
                        buttonText = "Done";
                        buttonClass = "btn-success";
                        ImgClass= "bg-success";
                        imageSrc = "/images/view.png";
                    }
                
                    let row = `
                        <tr>
                            <td class="inspection_list" data-inspections='${JSON.stringify(mission.inspection_types)}'>${inspectionTypes}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td class="locations_list" data-locations='${JSON.stringify(mission.locations)}'>${locations}</td>
                            <td>${mission.status}</td>
                            <td>
                          
                                <img src="${imageSrc}" data-id="${mission.id}" class="img-fluid actions pilotEvents mission_status ${ImgClass}" >
                            </td>
                        </tr>
                    `;
                
                    $('#missionTableBody').append(row);
                });
                
            }
        });
    }
    





    // Handle mission status button click
    $(document).on('click', '.mission_status', function () {
        let image = $(this); // .pilotEvents image
        let missionId = image.data('id');
        let row = image.closest('tr');
        let currentSrc = image.attr("src") // Ensure case-insensitivity
        let wrapper = image.closest('.status-icon-wrapper');
        let newStatus = "";
        let newImageSrc = "";
        let newBgClass = "";
        if (currentSrc.includes("start.png")) {
            newStatus = "In Progress";
            newImageSrc = "/images/finish.png";
            newBgClass = "bg-danger";
        } else if (currentSrc.includes("finish.png")) {
            newStatus = "Awaiting Report";
            newImageSrc = "/images/uploadreport.png";
            newBgClass = "bg-danger";
        } else if (currentSrc.includes("uploadreport.png")) {
            // Show modal for report
            $("#inspectionLocationGroup .inspection-location-item").not(":first").remove();
            $('#addReportModal').modal('show');
            $('#mission_id').val(missionId).trigger('change');
    
            // Get related inspections and locations
            let inspectionsData = row.find('.inspection_list').data('inspections');
            let locationData = row.find('.locations_list').data('locations');
    
            if (inspectionsData) {
                $('#inspection_id').empty().append('<option value="">Inspection Type</option>');
                inspectionsData.forEach(item => {
                    $('#inspection_id').append(`<option value="${item.id}">${item.name}</option>`);
                });
            }
    
            if (locationData) {
                $('#location_id').empty().append('<option value="">Select Location</option>');
                locationData.forEach(loc => {
                    $('#location_id').append(`<option value="${loc.id}">${loc.name}</option>`);
                });
            }
    
            return; // Stop here - no status update when opening report modal
        } else if (currentSrc.includes("view.png")) {
            $('#viewReportModal').modal('show');
           
            fetchReports(missionId);
            return;
        } else {
            return; // Completed or unknown state
        }
    
        // Update the image
        image.attr("src", newImageSrc);
        wrapper.removeClass("bg-danger bg-warning bg-primary bg-success").addClass(newBgClass);
        // Update the status cell visually (optional)
        row.find("td:nth-child(5)").text(newStatus); // assuming 5th column is status
    
        // Send status update to backend
        $.ajax({
            url: "/pilot/missions/update-status",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                mission_id: missionId,
                status: newStatus
            },
            success: function () {
                fetchMissions(); // Optional: refresh table if needed
            },
            error: function () {
                alert("Failed to update mission status.");
            }
        });
    });
    


    function extractYouTubeID(url) {
        const match = url.match(/(?:youtube\.com\/.*v=|youtu\.be\/)([^&]+)/);
        return match ? match[1] : null;
    }
    

    // ✅ Fetch Reports
    function fetchReports(missionId = null) {
        $('#reportTableBody').html(`
            <tr><td colspan="8" class="text-center text-muted">Loading reports...</td></tr>
        `);
    
        $.ajax({
            url: "/pilot/reports",
            type: "GET",
            data: missionId ? { mission_id: missionId } : {},
            success: function (response) {
                // console.log("🚀 Reports Fetched:", response);
                $('#reportTableBody').empty();
    
                if (response.reports.length === 0) {
                    $('#reportTableBody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted">No reports submitted yet.</td>
                        </tr>
                    `);
                    return;
                }
                // console.log(response.reports[0].video_url); 
                const videoId = extractYouTubeID(response.reports[0].video_url);
                if (videoId) {
                    const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`;
                    $('#pilotVideo').attr('src', embedUrl);
                }
                
                $(".pilot_note").text(response.reports[0].description);                

                $.each(response.reports, function (index, report) {
                    // Top row: Summary info
                    let videoLink = report.video_url ? `<a href="${report.video_url}" target="_blank">Watch</a>` : "N/A";
                   
                    let summaryRow = `
                        <tr class=" text-white">
                            <td colspan="2"><strong>Report Ref:</strong> ${report.report_reference}</td>
                            <td colspan="2"><strong>Start:</strong> ${report.start_datetime}</td>
                            <td colspan="2"><strong>End:</strong> ${report.end_datetime}</td>
                            <td colspan="2" class="text-end">
                        
                                <img src="./images/edit.png" alt="" class=" edit-report img-fluid actions" data-id="${report.id}">
                                <img src="./images/delete.png" alt="" class="delete-report img-fluid actions" data-id="${report.id}">
                            </td>
                        </tr>
                    `;
                    $('#reportTableBody').append(summaryRow);
    
                    // Group images by inspection_type_id
                    const grouped = {};
                    report.images.forEach(img => {
                        const key = `${img.inspection_type_id}-${img.description}`;
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(img);
                    });
    
                    // Add rows for each group
                    $.each(grouped, function (key, imagesGroup) {
                        const firstImg = imagesGroup[0];
                        const description = firstImg.description || "No Description";
    
                        // let imagesHtml = imagesGroup.map(img => `
                        //     <img   src="/${img.image_path}" class="" style="width: 80px; height: 80px;">
                        // `).join("");
                        let imagesHtml = `
                        <div style="width: 100%; overflow-x: auto;">
                          <div class="image-scroll-wrapper">
                            ${imagesGroup.map(img => `
                              <img src="/${img.image_path}" class="" />
                            `).join("")}
                          </div>
                        </div>
                      `;


                    
                        let groupRow = `
                            <tr>
                                <td colspan="2">${firstImg.inspection_type.name}</td>
                                <td colspan="2">${firstImg.location.name}</td>
                                <td colspan="3">${description}</td>
                                <td colspan="3">${imagesHtml}</td>
                            </tr>
                        `;
                        $('#reportTableBody').append(groupRow);
                    });
                });
            },
            error: function () {
                $('#reportTableBody').html(`
                    <tr><td colspan="8" class="text-center text-danger">Error loading reports</td></tr>
                `);
            }
        });
    }
    


    // ✅ Handle Edit Report Button Click start
    $(document).on("click", ".edit-report", function () {
        let reportId = $(this).data("id");
        $('#viewReportModal').modal('hide');
    
        $.ajax({
            url: `/pilot/reports/${reportId}/edit`,
            type: "GET",
            success: function (response) {
                const report = response;
    
                // ✅ Show modal
                $('#updateReportModal').modal('show');
    
                // ✅ Fill general report data
                $('#edit_report_id').val(reportId);
                $('#edit_start_datetime').val(report.start_datetime);
                $('#edit_end_datetime').val(report.end_datetime);
                $('#edit_video_url').val(report.video_url);
                $('#edit_description').val(report.description);
    
                // ✅ Clear previous incident rows
                const groupContainer = $('#updateInspectionLocationGroup');
                groupContainer.empty();
    
                // ✅ Loop through incidents and build UI
                report.incidents.forEach((incident, index) => {
                    const inspectionOptions = report.inspections.map(ins =>
                        `<option value="${ins.id}" ${ins.id === incident.inspection_type_id ? 'selected' : ''}>${ins.name}</option>`
                    ).join("");
    
                    const locationOptions = report.locations.map(loc =>
                        `<option value="${loc.id}" ${loc.id === incident.location_id ? 'selected' : ''}>${loc.name}</option>`
                    ).join("");
    
                    const imagePreview = incident.images.map(img =>
                        `<img src="/${img}" width="80" height="80" style="object-fit:cover;" />`
                    ).join("");
    
                    const incidentHTML = `
                        <div class="col-lg-3 col-md-3 col-sm-6 mb-3 updateinspection-location-item">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <select class="form-select inspection_id dateInput form-control-lg" name="inspection_id[]" required>
                                        ${inspectionOptions}
                                    </select>
                                </div>
                                <div class="col-12 mb-2">
                                    <select class="form-select location_id dateInput form-control-lg" name="location_id[]" required>
                                        ${locationOptions}
                                    </select>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="image-upload-box border-secondary rounded p-3 text-center text-white" onclick="this.querySelector('input[type=file]').click()">
                                        <p class="mb-2">Click to Upload Images</p>
                                        <div class="image-preview d-flex flex-wrap gap-2 justify-content-start" data-image="${incident.images}">
                                            ${imagePreview}
                                        </div>
                                        <input type="file" class="form-control d-none images" name="images_${index}[]" multiple accept="image/*">
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <input type="text" class="form-control inspectiondescrption dateInput text-white form-control-lg" name="inspectiondescrption[]" value="${incident.description}" placeholder="Inspection Description">
                                </div>
                            </div>
                        </div>
                    `;
    
                    groupContainer.append(incidentHTML);
                });
            },
            error: function () {
                alert("❌ Failed to fetch report data for editing.");
            }
        });
    });
    
    
   
    
    // ✅ Helper function to populate dropdowns
    function populateDropdown(options, selectedValue) {
        return options.map(option => 
            `<option value="${option.id}" ${option.id == selectedValue ? 'selected' : ''}>${option.name}</option>`
        ).join("");
    }

    // update report
    $(document).on("submit", "#updateReportForm", function (e) {
        e.preventDefault();
    
        const reportId = $('#edit_report_id').val();
    
        const structuredData = {
            start_datetime: $('#edit_start_datetime').val(),
            end_datetime: $('#edit_end_datetime').val(),
            video_url: $('#edit_video_url').val(),
            description: $('#edit_description').val(),
            pilot_report_images: []
        };
    
        const formData = new FormData();
    
        $('#updateInspectionLocationGroup .updateinspection-location-item').each(function (index) {
            const $item = $(this);
    
            const inspectionId = $item.find('.inspection_id').val();
            const locationId = $item.find('.location_id').val();
            const description = $item.find('.inspectiondescrption').val();
            const fileInput = $item.find('input[type="file"]')[0];
            const hasNewImages = fileInput && fileInput.files.length > 0;
            
            let previousImage = $item.find('.image-preview').attr('data-image') || "N/A";
            const existingImages = [];

            $item.find('.image-preview img').each(function () {
                const src = $(this).attr('src');
                if (src && src.startsWith('data:image')) {
                    // New uploaded base64 image
                    existingImages.push(src);
                }
            });



    
            // Append new images to FormData
            if (hasNewImages) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append(`images_${index}[]`, fileInput.files[i]);
                }
            }
    
            structuredData.pilot_report_images.push({
                index: index,
                inspection_id: inspectionId,
                location_id: locationId,
                description: description,
                new_images: hasNewImages ? fileInput.files.length : 0,
                new_image_data: hasNewImages ? existingImages.filter(src => src.startsWith('data:image')) : [],
                previous_image: previousImage
            });
        });
    
        // Log final JSON structure
        console.log("📝 Final JSON to Submit:", JSON.stringify(structuredData, null, 2));
    
        formData.append("data", JSON.stringify(structuredData));
    
        $.ajax({
            url: `/pilot/reports/${reportId}/update`,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response.message);
                $('#updateReportModal').modal('hide');
                fetchReports();
            },
            error: function (xhr) {
                console.error("❌ Update Error:", xhr.responseText);
                alert("Failed to update the report.");
            }
        });
    });
    
// Submit Update Report Form

// Submit Update Report Form
// $(document).on("submit", "#updateReportForm", function (e) {
//     e.preventDefault();

//     const reportId = $('#edit_report_id').val();

//     const structuredData = {
//         start_datetime: $('#edit_start_datetime').val(),
//         end_datetime: $('#edit_end_datetime').val(),
//         video_url: $('#edit_video_url').val(),
//         description: $('#edit_description').val(),
//         pilot_report_images: []
//     };

//     const formData = new FormData();

//     $('#updateInspectionLocationGroup .updateinspection-location-item').each(function (index) {
//         const $item = $(this);

//         const inspectionId = $item.find('.inspection_id').val();
//         const locationId = $item.find('.location_id').val();
//         const description = $item.find('.inspectiondescrption').val();
//         const fileInput = $item.find('input[type="file"]')[0];
//         const hasNewImages = fileInput && fileInput.files.length > 0;

//         // Collect existing image previews
//         const existingImages = $item.find('.image-preview img').map(function () {
//             return $(this).attr('src');
//         }).get();

//         // Append new images to FormData
//         if (hasNewImages) {
//             for (let i = 0; i < fileInput.files.length; i++) {
//                 formData.append(`images_${index}[]`, fileInput.files[i]);
//             }
//         }

//         structuredData.pilot_report_images.push({
//             index: index,
//             inspection_id: inspectionId,
//             location_id: locationId,
//             description: description,
//             new_images: hasNewImages ? fileInput.files.length : 0,
//             existing_images: existingImages
//         });
//     });

//     // Log final JSON structure
//     console.log("📝 Final JSON to Submit:", JSON.stringify(structuredData, null, 2));

//     formData.append("data", JSON.stringify(structuredData));

//     // $.ajax({
//     //     url: `/pilot/reports/${reportId}/update`,
//     //     method: "POST",
//     //     data: formData,
//     //     processData: false,
//     //     contentType: false,
//     //     success: function (response) {
//     //         alert(response.message);
//     //         $('#updateReportModal').modal('hide');
//     //         fetchReports();
//     //     },
//     //     error: function (xhr) {
//     //         console.error("❌ Update Error:", xhr.responseText);
//     //         alert("Failed to update the report.");
//     //     }
//     // });
// });



    
        // for editing add adn remove row

        let editgroupIndex = 1; // Unique index for naming input fields dynamically

        // Function to add a new Incident Detail row dynamically
        $(document).on("click", ".addEditInspectionRow", function () {
            // Fetch available options from the first row in `#editInspectionLocationGroup`
            let firstRow = $("#editInspectionLocationGroup .editinspection-location-item:first");
        
            let inspectionOptions = firstRow.find(".inspection_id").html(); // Get all options for inspection
            let locationOptions = firstRow.find(".location_id").html(); // Get all options for location
        
            let newGroup = `
                <div class="row mb-3 editinspection-location-item">
                    <label class="form-label">Incident Detail</label>
                    <div class="col-md-3">
                        <select class="form-select inspection_id" name="inspection_id[]" required>
                            ${inspectionOptions} <!-- Inject fetched options here -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select location_id" name="location_id[]" required>
                            ${locationOptions} <!-- Inject fetched options here -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control inspectiondescrption" name="inspectiondescrption[]" placeholder="Inspection Description">
                    </div>
                    <div class="col-md-2">
                        <input type="file" class="form-control images" name="images_${editgroupIndex}[]" multiple accept="image/*">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-success addEditInspectionRow me-2">+</button>
                        <button type="button" class="btn btn-danger removeEditInspectionRow">-</button>
                    </div>
                </div>
            `;
        
            $("#editInspectionLocationGroup").append(newGroup);
            editgroupIndex++;
        });
        
        // Function to remove an Incident Detail row
        $(document).on("click", ".removeEditInspectionRow", function () {
            if ($(".editinspection-location-item").length > 1) {
                $(this).closest(".editinspection-location-item").remove();
            } else {
                alert("At least one Incident Detail is required.");
            }
        });
        // end here
    
   
    
    
   
    

    // ✅ Handle Edit Report Button Click end


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
                $('#viewReportModal').modal('hide');
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
                <div class="col-lg-3 col-md-3 col-sm-12  mb-4 inspection-location-item">
                    <div class="row ">
                            <div class="col-12 mb-2">
                                <select class="form-select inspection_id dateInput  dateInput form-control-lg" name="inspection_id[]" id="inspection_id" required></select> 
                            </div>
                            <div class="col-12 mb-2">
                                <select class="form-select location_id dateInput  dateInput form-control-lg" name="location_id[]" id="location_id" required></select> 
                            </div>
                            <div class="col-12 mb-2 ">
                                <div class="image-upload-box  border-secondary rounded p-3 text-center text-white" style="" onclick="this.querySelector('input[type=file]').click()">
                                    <p class="mb-2">Click to Upload Images</p>
                                    <div class="image-preview d-flex flex-wrap gap-2 justify-content-start"></div>
                                    <input type="file" class="form-control d-none images" name="images_${groupIndex}[]" multiple accept="image/*">
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control inspectiondescrption dateInput text-white form-control-lg" name="inspectiondescrption[]" placeholder="Inspection Description">
                            </div>
                        </div>
                </div>
        `;

        $("#inspectionLocationGroup").append(newGroup);
        updateDropdowns(); // Populate dropdowns with existing values
        groupIndex++;
    });

    // Function to remove an Incident Detail row
    $(document).on('click', '.removeInspectionRow', function () {
        // Target the container holding all inspection rows
        const $group = $('#inspectionLocationGroup');
    
        // Only remove if there's more than one item (optional safety check)
        if ($group.find('.inspection-location-item').length > 1) {
            $group.find('.inspection-location-item').last().remove();
        }
    });

 
        
    let updgroupIndex = 1; // Unique index for naming input fields dynamically

    // Function to add a new Incident Detail row dynamically
    $(document).on("click", ".updaddInspectionRow", function () {
        let firstRow = $("#updateInspectionLocationGroup .updateinspection-location-item:first");
    
        let inspectionOptions = firstRow.find(".inspection_id").html(); // Get all options for inspection
        let locationOptions = firstRow.find(".location_id").html(); // Get all options for location
        let newGroup = `
                <div class="col-lg-3 col-md-3 col-sm-12  mb-4 updateinspection-location-item">
                    <div class="row ">
                            <div class="col-12 mb-2">
                                <select class="form-select inspection_id dateInput   form-control-lg" name="inspection_id[]" id="inspection_id" required>
                                ${inspectionOptions} 
                                </select> 
                            </div>
                            <div class="col-12 mb-2">
                                <select class="form-select location_id dateInput  form-control-lg" name="location_id[]" id="location_id" required>
                                ${locationOptions}
                                </select> 
                            </div>
                            <div class="col-12 mb-2 ">
                                <div class="image-upload-box  border-secondary rounded p-3 text-center text-white" style="" onclick="this.querySelector('input[type=file]').click()">
                                    <p class="mb-2">Click to Upload Images</p>
                                    <div class="image-preview d-flex flex-wrap gap-2 justify-content-start"></div>
                                    <input type="file" class="form-control d-none images" name="images_${updgroupIndex}[]" multiple accept="image/*">
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control inspectiondescrption dateInput text-white form-control-lg" name="inspectiondescrption[]" placeholder="Inspection Description">
                            </div>
                        </div>
                </div>
        `;

        $("#updateInspectionLocationGroup").append(newGroup);
        updateDropdowns(); // Populate dropdowns with existing values
        updgroupIndex++;
    });
    $(document).on('click', '.updremoveInspectionRow', function () {
        // Target the container holding all inspection rows
        const $group = $('#updateInspectionLocationGroup');
    
        // Only remove if there's more than one item (optional safety check)
        if ($group.find('.updateinspection-location-item').length > 1) {
            $group.find('.updateinspection-location-item').last().remove();
        }
    });
    // $(document).on("click", ".removeInspectionRow", function () {
    //     if ($(".inspection-location-item").length > 1) {
    //         $(this).closest(".inspection-location-item").remove();
    //     } else {
    //         alert("At least one Incident Detail is required.");
    //     }
    // });

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
