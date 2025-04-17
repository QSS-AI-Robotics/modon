$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

        // Image preview logic
        $(document).on('change', '.images', function () {
            const previewContainer = $(this).closest('.image-upload-box').find('.image-preview');
            previewContainer.empty();
    
            const files = this.files;
            if (files.length > 0) {
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = $('<img>').attr('src', e.target.result).css({ width: '100px', height: '100px', objectFit: 'cover', borderRadius: '8px' });
                        previewContainer.append(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
        getRegionManagerMissions()
        function getRegionManagerMissions() {
            $(".mission-btn svg").attr({ "width": "16", "height": "16" });
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
        
            $.ajax({
                url: "/pilot/missions",
                type: "GET",
                success: function (response) {
                    console.log("mission detail", response);
                    $('#pilotTableBody').empty();
        
                    const userType = $('#mifu').text().trim();
        
                    if (!response.missions.length) {
                        $('#pilotTableBody').append(`
                            <div class="col-12 text-center my-4">No Missions Found !!!</div>
                        `);
                        return;
                    }
        
                    $.each(response.missions, function (index, mission) {
                        const inspection = mission.inspection_types[0] || {};
                        const inspectionName = inspection.name || 'N/A';
                        const inspectionId = inspection.id || '';
                        const locations = mission.locations.map(loc => loc.name).join(', ') || 'N/A';
        
                        const firstLocation = mission.locations[0] || {};
                        const firstAssignment = firstLocation.location_assignments?.[0];
                        const regionId = firstAssignment?.region?.id ?? '';
                        const regionName = firstAssignment?.region?.name ?? '';
    
                        const latitude = firstLocation.geo_location?.latitude || 'N/A';
                        const longitude = firstLocation.geo_location?.longitude || 'N/A';
    
                        const fullNote = mission.note || "No Notes";
        
                        let statusBadge = "";
                        switch (mission.status) {
                            case "Approved":        statusBadge = `<span class="badge p-2 bg-success">Approved</span>`; break;
                            case "Pending":         statusBadge = `<span class="badge p-2 bg-danger">Pending</span>`; break;
                            case "Rejected":        statusBadge = `<span class="badge p-2 bg-warning">Rejected</span>`; break;
                            case "In Progress":     statusBadge = `<span class="badge p-2 bg-info text-dark">In Progress</span>`; break;
                            case "Awaiting Report":statusBadge = `<span class="badge p-2 bg-primary">Awaiting Report</span>`; break;
                            case "Completed":       statusBadge = `<span class="badge p-2 bg-success">Completed</span>`; break;
                        }
        
                        const modonApproved  = mission.approval_status?.modon_admin_approved;
                        const regionApproved = mission.approval_status?.region_manager_approved;
        
                        const getStatusBadge = value => {
                            switch (value) {
                                case 1: return `<strong class="text-success">Approved</strong>`;
                                case 2: return `<strong class="text-danger">Rejected</strong>`;
                                default: return `<strong class="text-warning">Pending</strong>`;
                            }
                        };
        
                        const modonManagerStatus  = getStatusBadge(modonApproved);
                        const regionManagerStatus = getStatusBadge(regionApproved);
        
                        // ✅ Conditional edit/delete buttons (modon_admin only)
                        let editButton = '';
                        let deleteButton = '';
        
                        if (userType === 'modon_admin') {
                            if (mission.status === "Approved" || mission.status === "Rejected") {
                                editButton = `<img src="./images/edit.png" alt="Edit Disabled" class="img-fluid actions disabled-edit" style="opacity:0.5;cursor:not-allowed" title="Mission is approved — cannot edit">`;
                                deleteButton = `<img src="./images/delete.png" alt="Delete Disabled" class="img-fluid actions disabled-delete" style="opacity:0.5;cursor:not-allowed" title="Mission is approved — cannot delete">`;
                            } else {
                                editButton = `<img src="./images/edit.png" alt="Edit" class="edit-mission img-fluid actions" data-id="${mission.id}">`;
                                deleteButton = `<img src="./images/delete.png" alt="Delete" class="delete-mission img-fluid actions" data-id="${mission.id}">`;
                            }
                        }
        
                        // ✅ Conditional Approve/Reject buttons
                        let approvalButtons = '';
                        if (
                            mission.status === "Pending" &&
                            (
                                (userType === 'modon_admin' && modonApproved === 0) ||
                                (userType === 'region_manager' && regionApproved === 0)
                            )
                        ) {
                            approvalButtons = `
                                <strong class="text-end">
                                    <span class="badge p-2 px-3 me-2 hoverbtn bg-success approvalMission"
                                        data-mission-decision="approve" data-mission-id="${mission.id}">
                                        Approve
                                    </span>
                                    <span class="badge p-2 px-3 hoverbtn bg-danger approvalMission"
                                        data-mission-decision="reject" data-mission-id="${mission.id}">
                                        Reject
                                    </span>
                                </strong>
                            `;
                        }
        
                        // ✅ Final row HTML
                        const row = `
                            <div class="accordion-item" id="missionRow-${mission.id}" data-pilot-id="${mission.pilot_id}">
                                <h2 class="accordion-header" id="heading-${mission.id}">
                                    <button class="accordion-button collapsed d-flex px-3 py-2" type="button">
                                        <div class="row w-100 justify-content-between label-text">
                                            <div class="col-3 ps-2" data-name="${inspectionName}" data-inspectiontype-id="${inspectionId}">${inspectionName}</div>
                                            <div class="col-2 ps-4 mission_date">${mission.mission_date}</div>
                                            <div class="col-3 text-center">${locations}</div>
                                            <div class="col-2 text-center ps-5">${statusBadge}</div>
                                            <div class="col-2 text-center ps-5">
                                                ${editButton}
                                                ${deleteButton}
                                                <img src="./images/view.png" alt="View" class="view-mission img-fluid actions toggle-details" data-id="${mission.id}" data-bs-toggle="collapse" data-bs-target="#collapse-${mission.id}" aria-expanded="false" aria-controls="collapse-${mission.id}">
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-${mission.id}" class="accordion-collapse collapse" aria-labelledby="heading-${mission.id}" data-bs-parent="#pilotTableBody">
                                    <div class="accordion-body px-4 py-2 label-text">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="py-1">Program: ${inspectionName}</strong> 
                                            ${approvalButtons}
                                        </div>
                                        <strong class="py-3">Mission Date</strong>: ${mission.mission_date}<br>
        
                                        <strong class="py-3" 
                                            data-location-id="${firstLocation.id}" 
                                            data-region-id="${regionId}" 
                                            data-region-name="${regionName}">
                                            Locations
                                        </strong>: ${locations} ( ${regionName} )<br>
        
                                        <strong class="py-3"
                                            data-latitude="${latitude}" 
                                            data-longitude="${longitude}">
                                            Geo
                                        </strong>
                                        ${latitude}, ${longitude}<br>
        
                                        <strong class="py-3" data-pilot-id="${mission.pilot_info?.id}"> Pilot Name</strong>: ${mission.pilot_info?.name || 'N/A'}<br>
                                        <strong class="py-3">Mission Created By:</strong> <span class="text-capitalize">${mission.created_by.name}</span> (${mission.created_by.user_type})<br>
                                        <strong class="py-3">Note:</strong> ${fullNote}<br><br>
                                        <div class="d-flex">
                                            <div class="row w-100 align-items-center">
                                                <strong>Mission Approval</strong><br>
                                                <div class="col-6 label-text"><p>Modon Admin: ${modonManagerStatus}</p></div>
                                                <div class="col-6 label-text"><p>Region Manager: ${regionManagerStatus}</p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
        
                        $('#pilotTableBody').append(row);
                    });
                },
                error: function (xhr) {
                    console.error("❌ Error fetching missions:", xhr.responseText);
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to fetch missions.' });
                }
            });
        }
        
    // fetchMissions();

    // function fetchMissions() {
    //     $.ajax({
    //         url: "/pilot/missions",
    //         type: "GET",
    //         success: function (response) {
    //             $('#pilotTableBody').empty();
    //             $('#inspection_id').empty().append('<option value="">Select an Inspection</option>');
    
    //             if (response.missions.length === 0) {
    //                 $('#pilotTableBody').append(`
    //                     <tr>
    //                         <td colspan="6" class="text-center text-muted">
    //                             No new missions available.
    //                         </td>
    //                     </tr>
    //                 `);
    //                 $("#addReportBtn").prop("disabled", true);
    //                 return;
    //             }
    
    //             // console.log(response.missions);
    
    //             $.each(response.missions, function (index, mission) {
    //                 // let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
    //                 // let locations = mission.locations.map(loc => loc.name).join("<br>");
    //                 let inspectionTypesData = mission.inspection_types.map(type => type.name);
    //                 let locationsData = mission.locations.map(loc => loc.name);
    //                 // INSPECTION TYPES
    //                 let inspectionTypesHTMLData = '';
    //                 if (inspectionTypesData.length > 1) {
    //                     inspectionTypesHTMLData = `
    //                         <div class="dropdown d-flex align-items-center justify-content-center">
    //                             <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
    //                                 ${inspectionTypesData[0]}...
    //                             </span>
    //                             <ul class="dropdown-menu text-center">
    //                                 ${inspectionTypesData.map(type => `<li class="dropdown-item">${type}</li>`).join("")}
    //                             </ul>
    //                         </div>
    //                     `;
    //                 } else {
    //                     inspectionTypesHTMLData = `
    //                         <span class="text-white">${inspectionTypesData[0] || 'N/A'}</span>
    //                     `;
    //                 }

    //                 // LOCATIONS
    //                 let locationsHTMLData = '';
    //                 if (locationsData.length > 1) {
    //                     locationsHTMLData = `
    //                         <div class="dropdown d-flex align-items-center justify-content-center">
    //                             <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
    //                                 ${locationsData[0]}...
    //                             </span>
    //                             <ul class="dropdown-menu text-center">
    //                                 ${locationsData.map(loc => `<li class="dropdown-item">${loc}</li>`).join("")}
    //                             </ul>
    //                         </div>
    //                     `;
    //                 } else {
    //                     locationsHTMLData = `
    //                         <span class="text-white">${locationsData[0] || 'N/A'}</span>
    //                     `;
    //                 }


                
    //                 // Determine button text and image source based on mission status
    //                 let buttonText = "";
    //                 let buttonClass = "btn-danger";
    //                 let ImgClass = "bg-danger";
    //                 let imageSrc = "/images/start.png"; // Default
                
    //                 if (mission.status === "Pending") {
    //                     buttonText = "Start";
    //                     buttonClass = "btn-danger";
    //                     ImgClass = "bg-danger";
    //                     imageSrc = "/images/start.png";
    //                 } else if (mission.status === "In Progress") {
    //                     buttonText = "Finish";
    //                     buttonClass = "btn-warning";
    //                     ImgClass = "bg-warning";
    //                     imageSrc = "/images/finish.png";
    //                 } else if (mission.status === "Awaiting Report") {
    //                     buttonText = "Add Report";
    //                     buttonClass = "btn-primary";
    //                     ImgClass = "bg-primary";
    //                     imageSrc = "/images/uploadreport.png";
    //                 } else if (mission.status === "Completed") {
    //                     buttonText = "Done";
    //                     buttonClass = "btn-success";
    //                     ImgClass= "bg-success";
    //                     imageSrc = "/images/view.png";
    //                 }
    //              // <td class="inspection_list" data-inspections='${JSON.stringify(mission.inspection_types)}'>${inspectionTypes}</td>
    //                 // <td class="locations_list" data-locations='${JSON.stringify(mission.locations)}'>${locations}</td>
                   
    //                 let row = `
    //                     <tr>
    //                         <td class="inspection_list" data-inspections='${JSON.stringify(mission.inspection_types)}'>${inspectionTypesHTMLData}</td>
    //                         <td>${mission.start_datetime}</td>
    //                         <td>${mission.end_datetime}</td>
    //                         <td class="locations_list" data-locations='${JSON.stringify(mission.locations)}'>${locationsHTMLData}</td>
    //                         <td>${mission.status}</td>
    //                         <td>
                          
    //                             <img src="${imageSrc}" data-id="${mission.id}" class="img-fluid actions pilotEvents mission_status ${ImgClass}" >
    //                         </td>
    //                     </tr>
    //                 `;
                
    //                 $('#pilotTableBody').append(row);
    //             });
                
    //         }
    //     });
    // }
    





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
// ✅ Enhanced JavaScript: Submit Report with Full Validation
$('#addReportForm').on('submit', function (e) {
    e.preventDefault();

    const $errorDiv = $('#report-validation-errors');
    $errorDiv.addClass('d-none').text('');

    let formData = new FormData();
    let isValid = true;

    // ✅ Required fields
    const missionId = $('#mission_id').val();
    const start = $('#start_datetime').val();
    const end = $('#end_datetime').val();
    const videoUrl = $('#video_url').val();
    const description = $('#description').val();

    if (!start || !end || !videoUrl || !description) {
        isValid = false;
        $errorDiv.removeClass('d-none').text('Start/End time, Video link, and Notes are required.');
        return;
    }

    formData.append('_token', $('input[name="_token"]').val());
    formData.append('mission_id', missionId);
    formData.append('start_datetime', start);
    formData.append('end_datetime', end);
    formData.append('video_url', videoUrl);
    formData.append('description', description);

    // ✅ Dynamic incident validation
    $('.inspection-location-item').each(function (index) {
        const inspectionId = $(this).find(".inspection_id").val();
        const locationId = $(this).find(".location_id").val();
        const incidentDesc = $(this).find(".inspectiondescrption").val();
        const fileInput = $(this).find("input[type='file']")[0];
        const images = fileInput?.files;

        if (!inspectionId || !locationId || !incidentDesc || !images || images.length === 0) {
            isValid = false;
            $errorDiv.removeClass('d-none').text(`Incident #${index + 1} is missing required fields.`);
            return false;
        }

        formData.append(`inspection_id[${index}]`, inspectionId);
        formData.append(`location_id[${index}]`, locationId);
        formData.append(`inspectiondescrption[${index}]`, incidentDesc);

        for (let i = 0; i < images.length; i++) {
            formData.append(`images_${index}[]`, images[i]);
        }
    });

    if (!isValid) return;

    // ✅ Submit via AJAX
    $.ajax({
        url: '/pilot/reports/store',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: 'Report Submitted!',
                text: response.message || 'The report has been successfully submitted.',
                timer: 2000,
                showConfirmButton: false,
                background: '#101625',
                color: '#ffffff'
            });

            $('#addReportModal').modal('hide');
            $('#addReportForm')[0].reset();
            fetchMissions();
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
            $errorDiv.removeClass('d-none').text(errorMessage);
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
                                <input type="text" class="form-control inspectiondescrption dateInput text-white form-control-lg" name="inspectiondescrption[]" placeholder="Inspection Description" required>
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
