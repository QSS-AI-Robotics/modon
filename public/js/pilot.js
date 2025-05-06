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
        getPilotMissions()
        $("#filterPilotMission").on("change", function () {
            const date = $(this).val();
        
            // ✅ Show loading SweetAlert
            Swal.fire({
                title: 'Loading Missions...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        
            getPilotMissions({ date });
            Swal.close();

            // ✅ Optional: If your getPilotMissions already closes SweetAlert on success, you're done.
            // If not, you can also close it after the AJAX call completes.
        });
        
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
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
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
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>`;
        
            paginationHTML += `</ul></nav>`;
            paginationWrapper.html(paginationHTML);
        
            // Attach click event
            $('.page-link').on('click', function (e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                    getPilotMissions({ page });
                }
            });
        }
     

        $(".refreshIcon").on('click', function(){

            window.location.reload();
        })
        
        function getPilotMissions({ status = null, date = null, page = 1 } = {}) {
            $(".mission-btn svg").attr({ "width": "16", "height": "16" });
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
            const data = { page }; // ✅ Include page number
            if (status) data.status = status;
            if (date) data.date = date
            $.ajax({
                url: "/pilot/missions",
                type: "GET",
                data: data,
                success: function (response) {
                    console.log("mission details", response.data);
                    $('#pilotTableBody').empty();
        
                    const userType = $('#mifu').text().trim();
        
                    if (!response.data.length) {
                        $('#pilotTableBody').append(`
                            <div class="col-12 text-center my-4">No Missions Found !!!</div>
                        `);
                        return;
                    }
        
                    $.each(response.data, function (index, mission) {
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
                        const pilotApproved = mission.approval_status?.pilot_approved;
        
                        const getStatusBadge = value => {
                            switch (value) {
                                case 1: return `<strong class="text-success">Approved</strong>`;
                                case 2: return `<strong class="text-danger">Rejected</strong>`;
                                default: return `<strong class="text-warning">Pending</strong>`;
                            }
                        };
        
                        const modonManagerStatus  = getStatusBadge(modonApproved);
                        const regionManagerStatus = getStatusBadge(regionApproved);
                        const pilotApprovedStatus = getStatusBadge(pilotApproved);
        
                        // ✅ Conditional edit/delete buttons (modon_admin only)
                        let editButton = '';
                        let deleteButton = '';
                        let uploadReportButton = '';
                        let viewReportButton = '';
        
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
                        // ✅ Conditional Approve/Reject buttons for Pilot
                        let approvalButtons = '';
                        if (pilotApproved === 0) {
                            approvalButtons = `
                                <strong class="text-end">
                                    <span class="badge p-2 px-3 me-2 hoverbtn bg-success approvalMissionbyPilot"
                                        data-mission-decision="approve" data-mission-id="${mission.id}">
                                        Approve
                                    </span>
                                    <span class="badge p-2 px-3 hoverbtn bg-danger approvalMissionbyPilot"
                                        data-mission-decision="reject" data-mission-id="${mission.id}">
                                        Reject
                                    </span>
                                </strong>
                            `;
                        }

                        if(mission.report_submitted === 1){
                            viewReportButton = `<img src="./images/view-report.png"  class="viewReportIcon img-fluid actions" data-id="${mission.id}">`;
                          
                        }
                        if(modonApproved === 1 && regionApproved === 1 && pilotApproved ===1 && mission.report_submitted === 0){
                            uploadReportButton = `<img src="./images/add-report.png" alt="View" class="addReportIcon img-fluid actions toggle-details" data-id="${mission.id}">`;
                            
                          
                        }
        
                        // ✅ Final row HTML
                        const row = `
                            <div class="accordion-item" id="missionRow-${mission.id}" data-mission-id=${mission.id} data-pilot-id="${mission.pilot_id}">
                                <h2 class="accordion-header" id="heading-${mission.id}">
                                    <button class="accordion-button collapsed d-flex px-3 py-2" type="button">
                                        <div class="row w-100 justify-content-between label-text">
                                            <div class="col-3 ps-2" data-incident-name="${inspectionName}" data-inspectiontype-id="${inspectionId}">${inspectionName}</div>
                                            <div class="col-2 ps-4 mission_date">${mission.mission_date}</div>
                                            <div class="col-3 text-center" data-location-name="${locations}">${locations}</div>
                                            <div class="col-2 text-center ps-5">${statusBadge}</div>
                                            <div class="col-2 text-center ps-5">
                                             
                                                <img src="./images/view.png" alt="View" class="view-mission img-fluid actions toggle-details" data-id="${mission.id}" data-bs-toggle="collapse" data-bs-target="#collapse-${mission.id}" aria-expanded="false" aria-controls="collapse-${mission.id}">
                                                ${uploadReportButton}
                                               
                                                ${viewReportButton}
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
                                        <strong class="py-3" data-mission-date="${mission.mission_date}">Mission Date</strong>: ${mission.mission_date}<br>
        
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
                                        <span data-geo-locations="${latitude}-${longitude}">${latitude}, ${longitude}</span><br>
        
                                        <strong class="py-3" data-pilot-id="${mission.pilot_info?.id}" data-pilot-name="${mission.pilot_info?.name}"> Pilot Name</strong>: <span data-pilot-name="${mission.pilot_info?.name}"></span>${mission.pilot_info?.name || 'N/A'}<br>
                                        <strong class="py-3">Mission Created By:</strong> <span class="text-capitalize" data-misison-created-by-name="${mission.created_by.name}">${mission.created_by.name}</span> (${mission.created_by.user_type})<br>
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
                    renderMissionPagination(response);
                },
                error: function (xhr) {
                    console.error("❌ Error fetching missions:", xhr.responseText);
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to fetch missions.' });
                }
            });
        }
        $(document).on('click', '.approvalMissionbyPilot', function () {
            const missionId = $(this).data("mission-id");
            const decision = $(this).data("mission-decision");

            const container = $(this).closest(".accordion-item");

            // Program
            const program = container
                .find("[data-incident-name]")
                .data("incident-name");

            // Mission Date
            const missionDate = container.find(".mission_date").text().trim();

            // Geo
            const geoElement = container.find("[data-latitude]");
            const latitude = geoElement.data("latitude");
            const longitude = geoElement.data("longitude");

            // Location (City and Region)
            const locationLabel = container.find("[data-location-id]");
            const locationText = locationLabel[0].nextSibling?.nodeValue?.trim() || "";
            const city = locationText.split("(")[0]?.trim();
            const region = locationText.match(/\(([^)]+)\)/)?.[1]?.trim();

            // Mission Created By
            const createdBySpan = container
                .find('strong:contains("Mission Created By:")')
                .next("span");
            const createdBy = createdBySpan.text().trim();
            const createdByRole = createdBySpan[0].nextSibling?.nodeValue
                ?.replace(/[()]/g, "")
                .trim();

            // Note
            const noteText = container
                .find('strong:contains("Note:")')[0]
                .nextSibling?.nodeValue?.trim();
            // Construct JSON object
            const missionDetails = {
                missionId,
                decision,
                program,
                missionDate,
                location: {
                    city,
                    region,
                },
                geolocation: {
                    latitude,
                    longitude,
                },
                createdBy: {
                    name: createdBy,
                    role: createdByRole,
                },
                note: noteText,
            };

            console.log(JSON.stringify(missionDetails, null, 4)); // Pretty print

            if (!missionId || !decision) {
                return Swal.fire(
                    "Error",
                    "Missing mission ID or decision",
                    "error"
                );
            }

            const isApproval = decision === "approve";
            const actionText = isApproval ? "Approve" : "Reject";
            const confirmButtonColor = isApproval ? "#28a745" : "#dc3545";

            Swal.fire({
                title: `${actionText} Mission?`,
                text: `Are you sure you want to ${actionText.toLowerCase()} this mission?`,
                icon: isApproval ? "success" : "warning",
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: "#6c757d",
                confirmButtonText: `Yes, ${actionText}`,
            }).then((result) => {
                if (!result.isConfirmed) return;

                // ✅ If approving, go straight to AJAX
                if (isApproval) {
                    submitPilotApproval(missionId, decision,null, missionDetails);
                } else {
                    // ❌ If rejecting, ask for reason
                    Swal.fire({
                        title: "Rejection Reason",
                        input: "textarea",
                        inputLabel:
                            "Please explain why you’re rejecting this mission",
                        inputPlaceholder: "Enter reason here...",
                        inputAttributes: {
                            "aria-label": "Rejection reason",
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return "Rejection reason is required!";
                            }
                        },
                        showCancelButton: true,
                        confirmButtonText: "Submit Rejection",
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                    }).then((rejectResult) => {
                        if (rejectResult.isConfirmed) {
                            submitPilotApproval(
                                missionId,
                                decision,
                                rejectResult.value,
                                missionDetails
                            );
                        }
                    });
                }
            });
        });


        function submitPilotApproval(missionId, decision, rejectionNote = null, missioninfo = null) {
            $.ajax({
                url: `/pilot/${missionId}/pilot-decision`,
                method: 'POST',
                data: {
                    mission_id: missionId,
                    decision: decision,
                    rejection_note: rejectionNote,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log("🚀 Mission Decision Updated:", response);
          
                    let recipients = [...new Set(response.allmails.map(user => user.email))];

                   


                    Swal.fire('Success', response.message || 'Decision updated!', 'success');
                    getPilotMissions();

                  
                    sendApprovalNotification({
                        mission: response,
                        recipients: recipients,
                        decision: decision,
                        missioninfo: missioninfo
                    });

                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                }
            });
        }
        $(document).on('click', '.viewReportIcon', function () {
            const missionRow = $(this).closest(".accordion-item");
            const missionId = missionRow.attr("id").split("-")[1]; // e.g., id="missionRow-3"
        
            // Prefill program/region/location
            const inspectionName = missionRow.find("[data-incident-name]").data("incident-name");
            const missionDate = missionRow.find("[data-mission-date]").data("mission-date");
            console.log("missionDate", missionDate);
            const regionName = missionRow.find("[data-region-name]").data("region-name");
            const locationName = missionRow.find("[data-location-name]").data("location-name");

            const MissionCreatedName = missionRow.find("[data-misison-created-by-name]").data("misison-created-by-name");
            const GeoLocation = missionRow.find("[data-geo-locations]").data("geo-locations");
            const PilotName = missionRow.find("[data-pilot-name]").data("pilot-name");
  
        
            $("#viewOwnerInfo").text(MissionCreatedName);
            $("#viewpilotInfo").text(PilotName);
 
            $("#viewprogramInfo").text(inspectionName);
            $("#viewregionInfo").text(regionName);
            $("#viewlocationInfo").text(locationName);
            $("#viewgeoInfo").text(GeoLocation);
            
            $("#viewmissionDateInfo").text(missionDate);
            $("#viewReportForm #mission_id").val(missionId);
        
            // Clear previous content
            $('#viewReportModal #description').html('');
            $('#viewReportModal #missionReportImages').empty();
            // $('#viewReportModal #pilotVideo').attr('src', '');
        
            $.ajax({
                url: '/pilot/fetchReportByMission',
                method: 'GET',
                data: { mission_id: missionId },
                success: function (res) {
                    const report = res.report;
        
                    $('#viewReportModal #description').html(report.description || 'N/A');
                    $('#viewReportModal .editReportbtn').attr('data-report-id', report.id);
                    $('#viewReportModal .deleteReportbtn').attr('data-report-id', report.id);
                    $('#viewReportModal .deleteReportbtn').attr('data-mission-id', missionId);
                    $('#viewReportModal #viewvideolinkInfo').attr('data-video-url', report.video_url);
                    $('#viewvideolinkInfo').text(report.video_url);
                    // if (report.video_url) {
                    //     const videoId = extractYouTubeID(report.video_url);
                    //     const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`;
                    //     $('#viewReportModal #pilotVideo').attr('src', embedUrl);
                    // }
        
                    if (report.images && report.images.length) {
                        report.images.forEach(img => {
                            const imgEl = `<img src="/${img.image_path}" alt="Report Image" data-image-id="${img.id}" class="img-thumbnail m-1 report-image" style="max-width: 150px;">`;
                            $('#viewReportModal #missionReportImages').append(imgEl);
                        });
                    } else {
                        $('#viewReportModal #missionReportImages').html('<p class="text-muted">No images uploaded.</p>');
                    }
        
                    $('#viewReportModal').modal('show');
                },
                error: function (err) {
                    console.error("Error fetching report:", err);
                    $('#viewReportModal #description').html('<p class="text-danger">Error loading report.</p>');
                }
            });
        });
        function extractYouTubeID(url) {
            const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|embed\/)([^&?/]+)/);
            return match ? match[1] : null;
        }

    
    $(document).on('click', '#missionReportImages img', function () {
        const imageSrc = $(this).attr('src');
        $('#fullscreenImage').attr('src', imageSrc);
        $('#fullscreenImageModal').removeClass('d-none');
    });
    
    // Close fullscreen modal
    $(document).on('click', '.fullscreen-image-modal .close-btn', function () {
        $('#fullscreenImageModal').addClass('d-none');
    });

    $(document).on('click', '.addReportIcon', function () {
        const missionRow = $(this).closest(".accordion-item");
        const missionId = missionRow.attr("id").split("-")[1]; // Extract from id="missionRow-123"

        const inspectionName = missionRow.find("[data-incident-name]").data("incident-name");
        const regionName = missionRow.find("[data-region-name]").data("region-name");
        const locationName = missionRow.find("[data-location-name]").data("location-name");
        const MissionCreatedName = missionRow.find("[data-misison-created-by-name]").data("misison-created-by-name");
        const GeoLocation = missionRow.find("[data-geo-locations]").data("geo-locations");
        const PilotName = missionRow.find("[data-pilot-name]").data("pilot-name");
        const MissionDate = missionRow.find("[data-mission-date]").data("mission-date");
       
        // Update display areas
        $("#programInfo").text(inspectionName);
        $("#regionInfo").text(regionName);
        $("#locationInfo").text(locationName);

        $("#missionCreatefInfos").val(MissionCreatedName);
        $("#pilotInfos").val(PilotName);
        $("#geolocationinfos").val(GeoLocation);
        $("#dateInfos").val(MissionDate);
        //console.log(MissionCreatedName, GeoLocation, PilotName, MissionDate);
        // Update hidden mission ID input
        $('#addReportModal #mission_id').val(missionId);
        $('#addReportModal').modal('show');
    });

    // Updated JS handler for submitting pilot report
    $(document).on('submit', '#addReportForm', function (e) {

        e.preventDefault();
    
        const $errorDiv = $('#report-validation-errors');
        $errorDiv.addClass('d-none').text('');
    
        const missionId   = $('#addReportModal #mission_id').val();
        const videoUrl    = $('#addReportModal #video_url').val();
        const description = $('#addReportModal #description').val();

        const programInfo = $('#addReportModal #programInfo').text();
        const regionInfo = $('#addReportModal #regionInfo').text();
        const locationInfo = $('#addReportModal #locationInfo').text();
        //hidden 
        const createdBy = $('#addReportModal #missionCreatefInfos').val();
        const geoLocations = $('#addReportModal #geolocationinfos').val();
        const pilotname = $('#addReportModal #pilotInfos').val();
        const dateInfos = $('#addReportModal #dateInfos').val();
        //console.log("hidden", createdBy, geoLocations, pilotname);


        const imageInput  = $('.images')[0];
        const images      = imageInput?.files;
      
        let isValid = true;
        if (!missionId || !videoUrl || !description) {
            isValid = false;
            $errorDiv.removeClass('d-none').text('All fields  are required.');
            return;
        }
       
        const formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('mission_id', missionId);
        formData.append('video_url', videoUrl);
        formData.append('description', description);
        formData.append('createdBy', createdBy);
        formData.append('geoLocations', geoLocations);
        formData.append('pilotname', pilotname);
        formData.append('programInfo', programInfo);
        formData.append('regionInfo', regionInfo);
        formData.append('locationInfo', locationInfo);
        formData.append('dateInfo', dateInfos);
    
        // ✅ Append images under 'images_0[]' key (as expected by backend)
        for (let i = 0; i < images.length; i++) {
            formData.append('images_0[]', images[i]);
        }
    
        // ✅ Log the data
        console.log("📦 Form Data Submission");
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}:`, pair[1]);
        }
        // ✅ Show loading SweetAlert before AJAX starts
        Swal.fire({
            title: 'Submitting Report...',
            text: 'Please wait while your report is being submitted.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

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
                }).then(() => {
                    $('#addReportModal').modal('hide');
                    $('#addReportForm')[0].reset();
                    $('.image-preview').empty();
                    getPilotMissions();
        
                    // Prepare recipients from response.users_emails
                    const realRecipients = (response.users_emails || []).map(u => u.email);
                    console.log("Real recipients for report notification:", realRecipients);
        
                    // Dummy recipients for testing
                    const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
        
                    sendReportNotification({
                        action: 'added',
                        missionData: response.mission_data,
                        recipients: dummyRecipients, // Use dummy for now
                        report: response.report
                    });
                });
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                $errorDiv.removeClass('d-none').text(errorMessage);
            }
        });
    });
    
    

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
                // const videoId = extractYouTubeID(response.reports[0].video_url);
                // if (videoId) {
                //     const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`;
                //     $('#pilotVideo').attr('src', embedUrl);
                // }
                $('#viewvideolinkInfo').text(response.reports[0].video_url);
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

    $('.search-input').on('keyup', function () {
        let query = $(this).val().toLowerCase();

        $('#pilotTableBody .accordion-item').each(function () {
            const itemText = $(this).text().toLowerCase();

            if (itemText.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });


    // edit and update report functions 
    $(document).on('click', '.editReportbtn', function (e) {
        e.preventDefault();
    
        const reportId = $(this).data('report-id');
    
        // Set hidden fields
        $('#edit_report_id').val(reportId);
        $('#edit_mission_id').val($('#mission_id').val());
    
        // Get data from viewReportModal and populate editReportModal
        $('#editProgramInfo').text($('#viewprogramInfo').text());
        $('#editRegionInfo').text($('#viewregionInfo').text());
        $('#editLocationInfo').text($('#viewlocationInfo').text());
    
        
        $('#edit_video_url').val($('#viewvideolinkInfo').text());
        


        const description = $('#description').html();
        $('#edit_description').val(description);
    
        // Handle current images
        const imageContainer = $('#editCurrentImages');
        imageContainer.empty();
        $('#missionReportImages img').each(function () {
            const imgSrc = $(this).attr('src');
            const imageId = $(this).data('image-id') || ''; // assuming you're using data-image-id
            imageContainer.append(`
                <div class="position-relative">
                    <img src="${imgSrc}" class="img-thumbnail" style="height:100px;" id="${imageId}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-existing-image" data-image-id="${imageId}">
                        &times;
                    </button>
                </div>
            `);
        });
    
        // Hide the view modal and show the edit modal
        $('#viewReportModal').modal('hide');
        $('#editReportModal').modal('show');
    });
    
    
    // Remove image from view and mark for deletion
    let imagesToRemove = [];

    $(document).on('click', '.remove-existing-image', function () {
        const imageId = $(this).data('image-id');
        console.log('Clicked image ID:', imageId);
    
        if (imageId) {
            imagesToRemove.push(imageId);
        }
    
        console.log("Current imagesToRemove:", imagesToRemove); // <- ADD THIS
        $(this).parent().remove();
    });
    
    // Preview and track new images
    $(document).on('change', 'input[name="new_images[]"]', function () {
        const previewContainer = $(this).siblings('.new-image-preview');
        previewContainer.empty(); // Clear previous previews
    
        const files = this.files;
    
        if (files.length === 0) {
            console.log("No new files selected.");
            return;
        }
    
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
    
            reader.onload = function (e) {
                const img = `<img src="${e.target.result}" class="img-thumbnail" style="height:100px;">`;
                previewContainer.append(img);
            };
    
            reader.readAsDataURL(file);
        }
    
        console.log("Selected new images:", files);
    });
    
    // Submit update form
    $('#editReportForm').on('submit', function (e) {
        e.preventDefault();
    
        const formData = new FormData(this);
    
        // Append removed image IDs as a JSON string
        formData.append('removed_images', JSON.stringify(imagesToRemove));
    
        // Debug FormData contents
        console.log('FormData being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ':', pair[1]);
        }
    
        // $.ajax({
        //     url: '/report/updatemissionreport', // ✅ NEW ENDPOINT
        //     method: 'POST',
        //     data: formData,
        //     contentType: false,
        //     processData: false,
        //     success: function (res) {
        //         $('#editReportModal').modal('hide');
        //         imagesToRemove = [];
        //         $('input[name="new_images[]"]').val('');
        //         $('.new-image-preview').empty();
        //     },
        //     error: function (err) {
        //         $('#edit-validation-errors').removeClass('d-none').text('Something went wrong!');
        //     }
        // });
        $.ajax({
            url: '/report/updatemissionreport', // or your actual update endpoint
            method: 'POST',
            data: formData, // or your data object
            contentType: false,
            processData: false,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Report Updated!',
                    text: response.message || 'The report has been successfully updated.',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#101625',
                    color: '#ffffff'
                }).then(() => {
                    $('#editReportModal').modal('hide');
                    $('#editReportForm')[0].reset();
                    $('.image-preview').empty();
                    getPilotMissions();
        
                    // Prepare recipients from response.users_emails
                    const realRecipients = (response.users_emails || []).map(u => u.email);
                    console.log("Real recipients for report update notification:", realRecipients);
        
                    // Dummy recipients for testing
                    const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
        
                    // Prepare missionData for email content
                    const missionData = {
                        pilotname: response.mission_data.mission?.pilot_name || 'N/A',
                        regionInfo: response.mission_data.region?.name || 'N/A',
                        locationInfo: Object.values(response.mission_data.locations || {}).join(', ') || 'N/A',
                        dateInfo: response.mission_data.mission?.mission_date || 'N/A',
                        programInfo: Object.values(response.mission_data.inspection_types || {}).join(', ') || 'N/A',
                        createdBy: response.mission_data.user?.name || 'N/A',
                        geoLocations: (response.mission_data.geo_locations && response.mission_data.geo_locations.length)
                            ? response.mission_data.geo_locations.map(g => `${g.latitude},${g.longitude}`).join(' | ')
                            : 'N/A'
                    };
        
                    sendReportNotification({
                        action: 'updated',
                        missionData: missionData,
                        recipients: dummyRecipients, // Use dummy for now
                        report: response.report
                    });
                });
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                $('#edit-validation-errors').removeClass('d-none').text(errorMessage);
            }
        });
        
    });

    // $(document).on('click', '.deleteReportbtn', function (e) {
    //     e.preventDefault();
    //     const reportId = $(this).data('report-id');
    //     const missionId = $(this).data('mission-id');
    
    //     if (!reportId || !missionId) {
    //         Swal.fire('Error', 'Missing report or mission ID.', 'error');
    //         return;
    //     }
    
    //     Swal.fire({
    //         title: 'Are you sure?',
    //         text: 'This report and its images will be permanently deleted.',
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#d33',
    //         cancelButtonColor: '#3085d6',
    //         confirmButtonText: 'Yes, delete it!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 url: '/pilot/delMissionReport',
    //                 method: 'POST',
    //                 data: {
    //                     _token: $('meta[name="csrf-token"]').attr('content'),
    //                     report_id: reportId,
    //                     mission_id: missionId
    //                 },
    //                 success: function (response) {
    //                     Swal.fire('Deleted!', response.message, 'success');
    //                     $('#viewReportModal').modal('hide');
    //                     // You can also refresh the mission list or update UI here
    //                 },
    //                 error: function (xhr) {
    //                     Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete report.', 'error');
    //                 }
    //             });
    //         }
    //     });
    // });
    $(document).on('click', '.deleteReportbtn', function (e) {
        e.preventDefault();
        const reportId = $(this).data('report-id');
        const missionId = $(this).data('mission-id');
    
        if (!reportId || !missionId) {
            Swal.fire('Error', 'Missing report or mission ID.', 'error');
            return;
        }
    
        Swal.fire({
            title: 'Are you sure?',
            text: 'This report and its images will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/pilot/delMissionReport',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        report_id: reportId,
                        mission_id: missionId
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Report deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false,
                            background: '#101625',
                            color: '#ffffff'
                        }).then(() => {
                            $('#viewReportModal').modal('hide');
                            getPilotMissions();
    
                            // Prepare recipients from response.users_emails
                            const realRecipients = (response.users_emails || []).map(u => u.email);
                            console.log("Real recipients for report delete notification:", realRecipients);
    
                            // Dummy recipients for testing
                            const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
    
                            // Prepare missionData for email content
                            const missionData = {
                                pilotname: response.mission_data.mission?.pilot_name || 'N/A',
                                regionInfo: response.mission_data.region?.name || 'N/A',
                                locationInfo: Object.values(response.mission_data.locations || {}).join(', ') || 'N/A',
                                dateInfo: response.mission_data.mission?.mission_date || 'N/A',
                                programInfo: Object.values(response.mission_data.inspection_types || {}).join(', ') || 'N/A',
                                createdBy: response.mission_data.user?.name || 'N/A',
                                geoLocations: (response.mission_data.geo_locations && response.mission_data.geo_locations.length)
                                    ? response.mission_data.geo_locations.map(g => `${g.latitude},${g.longitude}`).join(' | ')
                                    : 'N/A'
                            };
    
                            sendReportNotification({
                                action: 'deleted',
                                missionData: missionData,
                                recipients: dummyRecipients, // Use dummy for now
                                report: null // No report object on delete
                            });
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete report.', 'error');
                    }
                });
            }
        });
    });
    
    function sendApprovalNotification({ mission, recipients, decision,missioninfo }) {

    // Determine the action and email content based on the decision
    const action = decision == "approve" ? 'approved' : 'rejected';
    const subject = `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}`;
    console.log("real recipients: ", recipients);

    const content = `
            <p>Hello,</p>

            <p>A mission has been <strong style="color:${action === 'approved' ? 'green' : 'red'}">${action}</strong> by  <strong>${mission.pilot_name}</strong> (Pilot) in the Modon dashboard.
            Please log in to your account to view the latest details.</p>

            <hr>

            <h3 style="margin-bottom: 5px;">📋 <u>Mission Details:</u></h3>
            <ul style="line-height: 1.6;">
                <li><strong>Mission Date:</strong> ${missioninfo.missionDate || 'N/A'}</li>
                <li><strong>Program:</strong> ${missioninfo.program || 'N/A'}</li>
                <li><strong>Region:</strong> ${missioninfo.location.region || 'N/A'}</li>
                <li><strong>City:</strong> ${missioninfo.location.city || 'N/A'}</li>
                <li><strong>Mission was createted by:</strong>
                    <ul>
                        <li><strong>Name:</strong> ${missioninfo.createdBy.name || 'N/A'}</li>
                        <li><strong>Role:</strong> ${missioninfo.createdBy.role || 'N/A'}</li>
                    </ul>
                </li>
                <li><strong>Geolocation:</strong>
                    <ul>
                        <li><strong>Longitude:</strong> ${missioninfo.geolocation.longitude || 'N/A'}</li>
                        <li><strong>Latitude:</strong> ${missioninfo.geolocation.latitude || 'N/A'}</li>
                    </ul>
                </li>
                ${action === 'rejected' ? `<li><strong>Rejection Reason:</strong> ${mission.rejection_note || 'No reason provided'}</li>` : ''}
            </ul>

            <p>For more information, please visit the mission dashboard.</p>

            <br>

            <p>Best regards,<br>
            <strong>Admin Team</strong></p>
            `;

        // ✅ Show loading modal
        Swal.fire({
            title: `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}...`,
            html: 'Please wait while emails are being sent...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
        // ✅ Send email request
        fetch('/send-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({ recipients: dummyRecipients, subject, content })
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                text: data.message || `Mission ${action} notification sent successfully.`,
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Email send error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Email Error!',
                text: 'An error occurred while sending the email.'
            });
        });
    }
function sendReportNotification({ action, missionData, recipients, report, callback }) {
    // Capitalize action for subject
    const actionText = action.charAt(0).toUpperCase() + action.slice(1);

    // Email subject
    const subject = `Mission Report ${actionText}`;

    // Email content
    const content = `
        <p>Hello,</p>
        <p>A mission report has been <strong>${action}</strong> by <strong>${missionData.pilotname || 'N/A'}</strong> (Pilot) in the Modon dashboard.</p>
        <hr>
        <h3>📋 <u>Mission Details:</u></h3>
        <ul>
            <li><strong>Mission Date:</strong> ${missionData.dateInfo || 'N/A'}</li>
            <li><strong>Program:</strong> ${missionData.programInfo || 'N/A'}</li>
            <li><strong>Region:</strong> ${missionData.regionInfo || 'N/A'}</li>
            <li><strong>City:</strong> ${missionData.locationInfo || 'N/A'}</li>
            <li><strong>Mission Created By:</strong> ${missionData.createdBy || 'N/A'}</li>
            <li><strong>Geolocation:</strong> ${missionData.geoLocations || 'N/A'}</li>
            <li><strong>Report Reference:</strong> ${report?.report_reference || 'N/A'}</li>
        </ul>
        <p>For more information, please visit the mission dashboard.</p>
        <br>
        <p>Best regards,<br><strong>Admin Team</strong></p>
    `;

    // Show loader while sending emails
    Swal.fire({
        title: `Mission Report ${actionText}`,
        text: 'Please wait while emails are being sent...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('/send-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({ recipients, subject, content })
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Email Sent!',
            text: data.message || `Mission report ${action} notification sent successfully.`,
            timer: 2000,
            showConfirmButton: false
        });
        if (typeof callback === 'function') callback();
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Email Error!',
            text: 'An error occurred while sending the email.'
        });
        if (typeof callback === 'function') callback(error);
    });
}
    
        $(document).on('click', '.downloadReportPilot', function(e) {
            e.preventDefault();

            // Fetch the information
            const missionOwner   = $("#viewOwnerInfo").text().trim();
            const pilot          = $("#viewpilotInfo").text().trim();
            const region         = $("#viewregionInfo").text().trim();
            const program        = $("#viewprogramInfo").text().trim();
            const location       = $("#viewlocationInfo").text().trim();
            const geoCoordinates = $("#viewgeoInfo").text().trim();
            const description    = $("#description").text().trim();
            const missiondate    = $("#viewmissionDateInfo").text().trim();
         
 

            // 📸 Fetch all image URLs inside #missionReportImages
            const images = [];
            $("#missionReportImages img.report-image").each(function() {
                const imgSrc = $(this).attr('src');
                if (imgSrc) {
                    images.push(imgSrc);
                }
            });

            // Prepare data object
            const missionData = {
                owner: missionOwner,
                pilot: pilot,
                region: region,
                program: program,
                location: location,
                geo: geoCoordinates,
                description: description,
                missiondate:missiondate,
                images: images,
            };

            console.log(missionData);

            // 🚨 Show SweetAlert loading
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while your report is being created.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send data to backend
            $.ajax({
                url: '/download-mission-pdf',
                method: 'POST',
                data: JSON.stringify(missionData),
                xhrFields: {
                    responseType: 'blob'
                },
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response, status, xhr) {
                    Swal.close(); 

                    const blob = new Blob([response], { type: 'application/pdf' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Mission_Report.pdf';
                    link.click();
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('Failed!', 'PDF download failed. Please try again.', 'error');
                    console.error('PDF download failed');
                }
            });
        });
});
