$(document).ready(function () {
    // CSRF Token Setup for AJAX


    getRegionManagerMissions();
    
    $(".refreshIcon").on('click', function(){

        window.location.reload();
    })
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let userType = $('#mifu').text().trim().toLowerCase();

    if (userType !== 'pilot') {
        $('.report-buttons').addClass('d-none');
    }
    loadInspectionTypes();

   
 
    // if (typeof updateLanguageTexts === "function") {
    //    console.log("now its time to update the language");
    //     updateLanguageTexts(currentLang);
    // }
       // Utility function to generate lang key
       function toLangKey(str) {
        return str
            .trim()
            .replace(/\b3\b/g, 'three')      // Replace standalone digit 3 with 'three'
            .replace(/3/g, 'three')          // Replace any 3 with 'three'
            .replace(/&/g, 'and')            // Replace & with and
            .replace(/\s+/g, '_');           // Replace spaces with underscores
    }



    function loadInspectionTypes() {
        $.get('/missions/inspection-data', function (res) {
            const container = $('#inspectionTypesContainer');
            container.empty();
        
            res.inspectionTypes.forEach(type => {
                const langKey = toLangKey(type.name);
                container.append(`
                    <div class="col-md-12 col-sm-12">
                        <div class="form-check"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-custom-class="custom-tooltip"
                            data-title="${type.description || ''}">
                            <input type="radio" class="form-check-input" name="inspection_type" value="${type.id}" id="inspection_${type.id}">
                            <label class="form-check-label checkbox-text" data-lang-key="${langKey}" for="inspection_${type.id}">${type.name}</label>
                        </div>
                    </div>
                `);
            });
        
            // ✅ Enable tooltips
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(el => {
                const content = el.getAttribute('data-title');
                new bootstrap.Tooltip(el, {
                    html: true,
                    title: `<strong class="text-dark">Mission Description:</strong><br>${content}`,
                    customClass: 'custom-tooltip',
                    trigger: 'hover focus' 
                });
            });
        
            // ✅ Container is fully ready now, so translate immediately
            let currentLang = localStorage.getItem("selectedLang") || "ar";
            //console.log('Calling updateLanguageTexts with:', currentLang);
            updateLanguageTexts(currentLang);
        });
        

    }

    
    
    function filterLocationsByRegionId(regionId) {
        const locationSelect = document.getElementById('location_id');
        if (!locationSelect) return;
    
        // Clone all original options on first call and store in DOM for reuse
        if (!locationSelect.dataset.originalOptionsStored) {
            const allOptions = Array.from(locationSelect.options).map(opt => opt.cloneNode(true));
            locationSelect.dataset.originalOptionsStored = 'true';
            locationSelect.dataset.allOptions = JSON.stringify(allOptions.map(opt => ({
                value: opt.value,
                text: opt.text,
                regionId: opt.getAttribute('data-region-id') || '',
                selected: opt.selected
            })));
        }
    
        const allOptions = JSON.parse(locationSelect.dataset.allOptions);
    
        // Clear current options
        locationSelect.innerHTML = '';
    
        // Filter and re-add options that match the region ID
        let hasMatch = false;
        allOptions.forEach(opt => {
            if (opt.regionId === regionId) {
                const newOption = document.createElement('option');
                newOption.value = opt.value;
                newOption.text = opt.text;
                newOption.setAttribute('data-region-id', opt.regionId);
                locationSelect.appendChild(newOption);
                hasMatch = true;
            }
        });
    
        // Disable if no matching locations
        locationSelect.disabled = !hasMatch;
    
        // Auto-select the first matching option (optional)
        if (hasMatch && locationSelect.options.length > 0) {
            locationSelect.selectedIndex = 0;
        }
    }

    $('#region_id').on('change', function () {
        let selectedRegionId = $(this).val();
        filterLocationsByRegionId(selectedRegionId)
    });

    const $regionSelect = $('#region_id');
    const regionOptionsCount = $regionSelect.find('option').length;

    // If there are multiple regions, run the filtering function
    if (regionOptionsCount > 1) {
        let selectedRegionId = $regionSelect.val();
        if (selectedRegionId) {
            filterLocationsByRegionId(selectedRegionId);
        }
    }





    $(document).on('click', '.approvalMission', function () {
        const $clickedBtn = $(this);
        const missionId = $clickedBtn.data('mission-id');
        const decision = $clickedBtn.data('mission-decision');
    
        // Scope to the container (accordion-body)
        const $container = $clickedBtn.closest('.accordion-body');
    
        // Program
        const program = $container.find('strong:contains("Program")').next('.grayishytext').text().trim();
    
        // Location (region and city)
        const $locationEl = $container.find('strong[data-location-id]');
        const regionName = $locationEl.data('region-name');
        const city = $locationEl.parent().find('.grayishytext').text().replace(/\s*\(.*\)/, '').trim();
        // Geo Coordinates
        const $geoEl = $container.find('strong[data-latitude]');
        const latitude = $geoEl.data('latitude');
        const longitude = $geoEl.data('longitude');
    
      
    // Construct JSON object
    const missionData = {
        missionId: missionId,
        decision: decision,
        program: program,
        location: {
            region: regionName,
            city: city
        },
        geoCoordinates: {
            latitude: latitude,
            longitude: longitude
        }
    };

    console.log("🚀 Mission Data:", missionData);
        if (!missionId || !decision) {
            return Swal.fire('Error', 'Missing mission ID or decision', 'error');
        }
    
        const isApproval = decision === 'approve';
        const actionText = isApproval ? 'Approve' : 'Reject';
        const confirmButtonColor = isApproval ? '#28a745' : '#dc3545';
    
        Swal.fire({
            title: `${actionText} Mission?`,
            text: `Are you sure you want to ${actionText.toLowerCase()} this mission?`,
            icon: isApproval ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText}`,
        }).then((result) => {
            if (!result.isConfirmed) return;
    
            // ✅ If approving, go straight to AJAX
            if (isApproval) {
                //alert("🚀 Mission Data:");
                submitApproval(missionId, decision,null, missionData);

            } else {
                // ❌ If rejecting, ask for reason
                Swal.fire({
                    title: 'Rejection Reason',
                    input: 'textarea',
                    inputLabel: 'Please explain why you’re rejecting this mission',
                    inputPlaceholder: 'Enter reason here...',
                    inputAttributes: {
                        'aria-label': 'Rejection reason'
                    },
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Rejection reason is required!';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit Rejection',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                }).then((rejectResult) => {
                    if (rejectResult.isConfirmed) {
                        submitApproval(missionId, decision, rejectResult.value,missionData);
                    }
                });
            }
        });
    });
    
    // ✅ Old Function to submit approval or rejection with optional note
    function submitApprovalOld(missionId, decision, rejectionNote = null,) {
        $.ajax({
            url: `/missions/${missionId}/decision`,
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
                console.log("🚀 Mission Decision Response:", response);
                Swal.fire('Success', response.message || 'Decision updated!', 'success');
                getRegionManagerMissions();
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
            }
        });
    }
 // ✅  Function to submit approval or rejection with optional note
 function submitApproval(missionId, decision, rejectionNote = null, mission_info=null) {
    $.ajax({
        url: `/missions/${missionId}/decision`,
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
            console.log("🚀 Mission Decision Response:", response);

            // Extract necessary values from the response
            const { approval_details, users_associated_with_region, pilot_email, admin_emails, user_type, current_user_email,all_emails} = response;
            const regionManagerApproved = approval_details.region_manager_approved;
            const modonAdminApproved = approval_details.modon_admin_approved;

            // Initialize recipients array
            let recipients = [];

            // Log the initial response for debugging
            console.log("🚀 Initial Response:", response);

            // Fetch emails from "users_associated_with_region" but skip the current user if they are "region_manager"
            if (user_type === "region_manager") {
                recipients = all_emails
                    .filter(user => user.user_type_name !== "region_manager") // Exclude region_manager
                    .map(user => user.email); // Extract emails
                console.log("✅ Recipients after excluding region_manager:", recipients);
            } else {
                recipients = all_emails.map(user => user.email); // Include all if not region_manager
                console.log("✅ Recipients without exclusion:", recipients);
            }

            // Add pilot email only if both approvals are 1
            if (regionManagerApproved === 1 && modonAdminApproved === 1) {
                recipients.push(pilot_email);
                console.log("✅ Pilot email added as both approvals are 1:", pilot_email);
            } else {
                console.log("❌ Pilot email not added as approvals are not both 1.");
            }

            // Always include admin emails
            const adminEmails = admin_emails.map(admin => admin.email);
            recipients = recipients.concat(adminEmails);
            console.log("✅ Admin emails added:", adminEmails);

            // Remove duplicates from recipients
            recipients = [...new Set(recipients)];
            console.log("✅ Final Recipients after removing duplicates:", recipients);

            // Skip sending email to the current user
            console.log("✅ Current User Email:", current_user_email);
            if (current_user_email) {
                recipients = recipients.filter(email => email !== current_user_email);
                console.log("✅ Recipients after excluding current user's email:", recipients);
            }

            // Call the new sendApprovalNotification function
            sendApprovalNotification({
                mission: response,
                recipients: recipients,
                decision: decision,
                missioninfo: mission_info
            });

            Swal.fire('Success', response.message || 'Decision updated!', 'success');
            getRegionManagerMissions();
        },
        error: function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
        }
    });
}
    
    
    










    // getMissionStats();
    // function getMissionStats() {
    //     $.ajax({
    //         url: "/missions/stats",
    //         type: "GET",
    //         success: function (response) {
    //             let totalMissions = response.total_missions || 0;
    //             let completedMissions = response.completed_missions || 0;
    //             let pendingMissions = totalMissions - completedMissions;
    
    //             // ✅ Avoid division by zero errors
    //             let completedPercentage = totalMissions > 0 ? (completedMissions / totalMissions) * 100 : 0;
    //             let pendingPercentage = totalMissions > 0 ? (pendingMissions / totalMissions) * 100 : 0;
    
    //             // ✅ Update Text
    //             $("#totalMissions").text(totalMissions);
    //             $("#completedMissions").text(completedMissions);
    //             $("#pendingMissions").text(pendingMissions);
    
    //             // ✅ Update Progress Bars
    //             $("#pendingMissionsBar").css("width", pendingPercentage + "%");
    //             $("#completedMissionsBar").css("width", completedPercentage + "%");
    //         },
    //         error: function (xhr) {
    //             console.error("❌ Error fetching mission stats:", xhr.responseText);
    //         }
    //     });
    // }
    // Trigger when a span is clicked
    $(".mstatus").on("click", function () {
        $(".mstatus").removeClass("activeStatus");
        $(this).addClass("activeStatus");

        const status = $(this).text().trim().toLowerCase(); // get text like "pending"
        const date = $("#filterMission").val(); // get date if selected

        getRegionManagerMissions({ status, date });
    });
    $("#filterMission").on("change", function () {
        const date = $(this).val();
        const status = $(".mstatus.activeStatus").text().trim().toLowerCase();
  
        getRegionManagerMissions({ status, date });
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
                getRegionManagerMissions({ page });
            }
        });
    }
        
    function getRegionManagerMissions({ status = null, date = null, page = 1 } = {}) {

        $(".mission-btn svg").attr({ "width": "16", "height": "16" });
        $("#addMissionForm").removeAttr("data-mission-id");
        $(".cancel-btn").addClass("d-none");
        const data = { page }; // ✅ Include page number
        if (status) data.status = status;
        if (date) data.date = date;
        
        $.ajax({
            url: "/getmanagermissions",
            type: "GET",
            data: data,
            success: function (response) {
                console.log("mission detail", response);
                $('#missionsAccordion').empty();
    
                const userType = $('#mifu').text().trim();
               
                const missions=response.data;
                if (!missions.length) {
                    $('#missionsAccordion').append(`
                        <div class="col-12 text-center my-4">No Missions Found !!!</div>
                    `);
                    return;
                }
    
                $.each(missions, function (index, mission) {
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
                        case "Approved":        statusBadge = `<span class="badge p-2 bg-success" data-lang-key="approved">Approved</span>`; break;
                        case "Pending":         statusBadge = `<span class="badge p-2 bg-danger" data-lang-key="pending">Pending</span>`; break;
                        case "Rejected":        statusBadge = `<span class="badge p-2 bg-warning" data-lang-key="rejected">Rejected</span>`; break;
                        case "In Progress":     statusBadge = `<span class="badge p-2 bg-info text-dark" data-lang-key="inProgress">In Progress</span>`; break;
                        case "Awaiting Report":statusBadge = `<span class="badge p-2 bg-primary" data-lang-key="awaitingReport">Awaiting Report</span>`; break;
                        case "Completed":       statusBadge = `<span class="badge p-2 bg-success" data-lang-key="completed">Completed</span>`; break;
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
                    let viewReportButton = '';
                    if (userType === 'modon_admin' || userType === 'region_manager') {
                        const reportSubmitted = mission.report_submitted;
                        const pilotApproved = mission.approval_status?.pilot_approved;
                    
                        const shouldDisable = reportSubmitted === 1 || reportSubmitted === 2 || pilotApproved === 1 || pilotApproved === 2;
                    
                        if (shouldDisable) {
                            // editButton = `<img src="./images/edit.png" alt="Edit Disabled" class="img-fluid actions disabled-edit" style="opacity:0.5;cursor:not-allowed" title="Mission cannot be edited">`;
                            // deleteButton = `<img src="./images/delete.png" alt="Delete Disabled" class="img-fluid actions disabled-delete" style="opacity:0.5;cursor:not-allowed" title="Mission cannot be deleted">`;
                          
                        }else {
                            if (mission.status !== 'Rejected') {
                                editButton = `<img src="./images/edit.png" alt="Edit" class="edit-mission img-fluid actions" data-id="${mission.id}">`;
                            }
                        
                            if ((userType === 'modon_admin' || userType === 'region_manager') && mission.status !== 'Rejected') {
                                deleteButton = `<img src="./images/delete.png" alt="Delete" class="delete-mission img-fluid actions" data-id="${mission.id}">`;
                            }
                        }
                    }
                    


 
                    // }
                    if(mission.report_submitted === 1){
                        viewReportButton = `<img src="./images/view-report.png" alt="Delete" class="viewMissionReport img-fluid actions" data-id="${mission.id}">`;
                      
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
                                        <div class="col-3 ps-2" data-name="${inspectionName}" data-incident-name="${inspectionName}" data-inspectiontype-id="${inspectionId}">${inspectionName}</div>
                                        <div class="col-2 ps-4 mission_date">${mission.mission_date}</div>
                                        <div class="col-3 text-center" data-location-name="${locations}">${locations}</div>
                                        <div class="col-2 text-center ps-5">${statusBadge}</div>
                                        <div class="col-2 text-center ps-5">
                                            ${editButton}
                                            ${deleteButton}
                                            <img src="./images/view.png" alt="View" class="view-mission img-fluid actions toggle-details" data-id="${mission.id}" data-bs-toggle="collapse" data-bs-target="#collapse-${mission.id}" aria-expanded="false" aria-controls="collapse-${mission.id}">
                                            ${viewReportButton}
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-${mission.id}" class="accordion-collapse collapse" aria-labelledby="heading-${mission.id}" data-bs-parent="#missionsAccordion">
                                <div class="accordion-body  px-4 py-2 label-text">
                                    <div class="row ">
                                        <div class="col-lg-6">
                                             <strong class="py-1" data-lang-key="program">Program<br></strong> 
                                             <span class="grayishytext">${inspectionName}</span>
                                        </div>
                                        <div class="col-lg-6 text-end">
                                             ${approvalButtons}
                                        </div>
                                        <div class="col-lg-4 ">
                                            <strong class="py-3" data-location-id="${firstLocation.id}" data-region-id="${regionId}" data-region-name="${regionName}" data-lang-key="locations"> Locations </strong><br>
                                            <span class="grayishytext"> ${locations} ( ${regionName} )</span>       
                                        </div>                                        
                                        <div class="col-lg-4 ">
                                         <strong class="py-3" data-lang-key="missionDate">Mission Date</strong><br><span  class="grayishytext" data-mission-date="${mission.mission_date}">${mission.mission_date}</span>
                                        </div>                                        
                                        <div class="col-lg-4 ">
                                            <strong class="py-3" data-latitude="${latitude}" data-longitude="${longitude}"data-lang-key="geoCoordinates">Geo Coordinates </strong><br>
                                            <span class="grayishytext" data-geolocationinfo="${latitude}-${longitude}">${latitude}, ${longitude}</span>
                                        </div>                                        
                                        <div class="col-lg-4 ">
                                            <strong class="py-3" data-pilot-id="${mission.pilot_info?.id}" data-lang-key="pilotName"> Pilot Name</strong><br> <span class="grayishytext" data-pilot-name="${mission.pilot_info?.name}">${mission.pilot_info?.name || 'N/A'}</span>
                                        </div> 
                                        <div class="col-lg-4 "> 
                                            <strong class="py-3"data-lang-key="missionCreatedBy">Mission Created By<br></strong> <span class="text-capitalize grayishytext" data-mission-created-by-name="${mission.created_by.name}">${mission.created_by.name}</span>(${mission.created_by.user_type}) 
                                        </div>
                                        <div class="col-lg-12 border-bottom"> 
                                            <strong class="py-3" data-lang-key="note">Note</strong><br><span class="grayishtext" data-mission-note="${fullNote}"> ${fullNote}
                                        </div> 
                                        <div class="col-lg-12">
                                            <div class="row w-100 align-items-center">
                                                <strong data-lang-key="missionApproval">Mission Approval</strong><br>
                                                <div class="col-4 label-text" ><p data-lang-key="test"><span data-lang-key="modonAdmin">Modon Admin:</span> ${modonManagerStatus}</p></div>
                                                <div class="col-4 label-text"><p><span data-lang-key="regionManager">Region Manager:</span> ${regionManagerStatus}</p></div>
                                                <div class="col-4 label-text"><p><span data-lang-key="pilot">Pilot:</span> ${pilotApprovedStatus}</p></div>
                                            </div>
                                        </div>                         
                                    </div>    
                              
                            </div>
                        </div>
                    `;
    
                    $('#missionsAccordion').append(row);
                });
                renderMissionPagination(response);
                let currentLang = localStorage.getItem("selectedLang") || "ar";
            //console.log('Calling updateLanguageTexts with:', currentLang);
            updateLanguageTexts(currentLang);
            },
            error: function (xhr) {
                console.error("❌ Error fetching missions:", xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to fetch missions.' });
            }
        });
    }
    

    $(document).on('click', '.viewMissionReport', function () {
        const missionRow = $(this).closest(".accordion-item");     
        let missionId = $(this).data('id');
       console.log(missionId)
        const inspectionName = missionRow.find("[data-incident-name]").data("incident-name");
        const regionName = missionRow.find("[data-region-name]").data("region-name");
        const locationName = missionRow.find("[data-location-name]").data("location-name");
        const pilotname = missionRow.find("[data-pilot-name]").data("pilot-name");
        const missionCreatedName = missionRow.find("[data-mission-created-by-name]").data("mission-created-by-name");
        const missionDate = missionRow.find("[data-mission-date]").data("mission-date");

        console.log(missionCreatedName)
        const geolocationinfo = missionRow.find("[data-geolocationinfo]").data("geolocationinfo");
    
        // Update display areas
        $("#viewprogramInfo").text(inspectionName);
        $("#viewregionInfo").text(regionName);
        $("#viewlocationInfo").text(locationName);
        $("#viewOwnerInfo").text(missionCreatedName);
        $("#viewgeoInfo").text(geolocationinfo);
        $("#viewpilotInfo").text(pilotname);
        $("#viewmissionDateInfo").text(missionDate);
    
        // Clear existing data
        $('#description').html('');
        $('#missionReportImages').empty();
        $('#pilotVideo').attr("src", "");
   
        // Call backend to get report by mission ID
        $.ajax({
            url: '/pilot/reports',
            type: 'GET',
            data: { mission_id: missionId },
            success: function (response) {
                console.log("misison Reported",response)
                if (!response.reports.length) {
                    $('#description').html('No report found for this mission.');
                    return;
                }
    
                const report = response.reports[0]; // Assuming only one report per mission
                $('#description').html(report.description || 'N/A');
    
                
                const videoId = extractYouTubeID(response.reports[0].video_url);
                if (videoId) {
                    const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`;
                    $('#pilotVideo').attr('src', embedUrl);
                }
                

    
                if (report.images && report.images.length) {
                    report.images.forEach(img => {
                        const imgEl = `<img src="/${img.image_path}" alt="Report Image" class="img-thumbnail m-1 report-image" style="max-width: 150px;">`;
                        $('#missionReportImages').append(imgEl);
                    });
                } else {
                    $('#missionReportImages').html('<p class="text-muted">No images uploaded.</p>');
                }
            },
            error: function (xhr) {
                console.error('Error fetching report:', xhr);
                $('#description').html('⚠ Error fetching report.');
            }
        });
    
        $('#viewMissionReportModal').modal('show');
    });
  
    $(document).on('click', '#missionReportImages img', function () {
        const imageSrc = $(this).attr('src');
        $('#fullscreenImage').attr('src', imageSrc);
        $('#fullscreenImageModal').removeClass('d-none');
    });
    
    // Close fullscreen modal
    $(document).on('click', '.fullscreen-image-modal .close-btn', function () {
        $('#fullscreenImageModal').addClass('d-none');
    });
    $(document).on('click', '.toggle-details', function () {
        const currentId = $(this).data('id');
        const $current = $(`.detail-row[data-id="${currentId}"] .detail-container`);
    
        // Close others
        $('.detail-container').not($current).slideUp();
    
        // Toggle current
        $current.stop(true, true).slideToggle();
    });
    
    

      
    
    
    // // Submit Add Mission Form via AJAX
    // Submit Add Mission Form via AJAX
$('#addMissionForm').on('submit', function (e) {
    e.preventDefault();

    
    const $form = $(this);
    const $errorDiv = $('#mission-validation-errors').addClass('d-none');
    
    const missionId = $form.attr("data-mission-id");
    const url = missionId ? "/missions/update" : "/missions/store";
    
    // ✅ Collect form data
    const inspectionType = $('input[name="inspection_type"]:checked').val();
    const locationId = $('#location_id').val();
    const region_id = $('#region_id').val();
    const selectedLocations = locationId ? [locationId] : [];
    const pilotId = $('#pilot_id').val();
    const latitude = $('#latitude').val();
    const longitude = $('#longitude').val();
    
    const formData = {
        mission_id: missionId,
        inspection_type: inspectionType,
        mission_date: $('#mission_date').val(),
        note: $('#note').val(),
        locations: selectedLocations,
        pilot_id: pilotId,
        latitude: latitude,
        longitude: longitude,
        region_id:region_id
    };
    
    // ✅ Validation - collect missing fields
    let errors = [];
    
    if (!formData.mission_date) errors.push("Mission date is required.");
    if (!inspectionType) errors.push("Please select an inspection type.");
    if (!region_id) errors.push("Please select an Region ");
    if (!pilotId) errors.push("Please select a pilot.");
    if (!latitude) errors.push("Please Enter a latitude.");
    if (!longitude) errors.push("Please Enter a longitude.");
    if (selectedLocations.length === 0) errors.push("Please select at least one location.");
    
    if (errors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information!',
            html: `<ul style="text-align:left;">${errors.map(err => `<li>${err}</li>`).join('')}</ul>`,
            background: '#101625',
            color: '#ffffff',
            confirmButtonColor: '#d33'
        });
        return;
    }
    
    // ✅ UI feedback during request
    const buttonText = missionId ? "Updating..." : "Creating...";
    const method = missionId ? "PUT" : "POST";
    
    $(".mission-btn span").text(buttonText);
    $(".mission-btn svg").attr({ "width": "20", "height": "20" });
    
    // ✅ Debug log
    // console.table(formData);
    
    // 👉 Continue with your AJAX submission below
    

    // ✅ Send AJAX
    $.ajax({
        url: url,
        type: method,
        data: formData,
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: 'Mission Saved!',
                text: response.message || 'Mission has been successfully created or updated.',
                timer: 2000,
                showConfirmButton: false,
                background: '#101625',
                color: '#ffffff'
            });

            // ✅ Reset form
            $form[0].reset();
            $('.location-checkbox').prop('checked', false);
            $('input[name="inspection_type"]').prop('checked', false);

            $form.removeAttr("data-mission-id");
            $("h6").text("Create New Mission");
            $(".mission-btn span").text("New Mission");
            console.log("Mission Response Data",response)
            if (Array.isArray(response.mission.allmails)) {
          
                const actionType = missionId ? 'Updated':'Created'
                sendMissionNotification({
                    mission: response.mission,
                    recipients: response.mission.allmails,
                    action: actionType
                });


            } else {
                console.error("Expected an array for 'allmails', got:", response.allmails);
            }
            getRegionManagerMissions();
            // getMissionStats();
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Something went wrong while saving the mission.',
                background: '#101625',
                color: '#ffffff'
            });
        }
    });
});


  

       // view Mission report
       $(document).on('click', '.view-mission-report', function () {
            let missionId = $(this).data('id');
            fetchReports(missionId)
        
        });
        function extractYouTubeID(url) {
            const match = url.match(/(?:youtube\.com\/.*v=|youtu\.be\/)([^&]+)/);
            return match ? match[1] : null;
        }
        function fetchReports(missionId = null) {
            $('#missionReportTableBody').html(`
                <tr><td colspan="8" class="text-center text-muted">Loading reports...</td></tr>
            `);
        
            $.ajax({
                url: "/pilot/reports",
                type: "GET",
                data: missionId ? { mission_id: missionId } : {},
                success: function (response) {
                    console.log("🚀mission Reports Fetched:", response);
                    $('#missionReportModal').modal('show');
                    $('#missionReportTableBody').empty();
        
                    if (response.reports.length === 0) {
                        $('#missionReportTableBody').append(`
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

                                </td>
                            </tr>
                        `;
                        $('#missionReportTableBody').append(summaryRow);
        
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
                            $('#missionReportTableBody').append(groupRow);
                        });
                    });
                },
                error: function () {
                    $('#missionReportTableBody').html(`
                        <tr><td colspan="8" class="text-center text-danger">Error loading reports</td></tr>
                    `);
                }
            });
        }

        $(document).on('click', '.view-mission', function () {
        
            resetValues();
        });
    // Delete Mission
    
    $(document).on('click', '.delete-mission', function () {
        const missionId = $(this).data('id');
        resetValues();
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This mission will be permanently deleted.",
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Reason for Deletion',
            inputPlaceholder: 'Type reason here...',
            inputAttributes: {
                'aria-label': 'Reason for deletion'
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteReason = result.value?.trim();
    
                $.ajax({
                    url: `/missions/${missionId}`,
                    type: "POST",
                    data: {
                        delete_reason: deleteReason
                    },
                    success: function (response) {
                        console.log(response)
                        sendMissionNotification({
                            mission: response.mission,
                            recipients: response.mission.allmails,
                            action: 'deleted'
                        });
                        // Swal.fire({
                        //     icon: 'success',
                        //     title: 'Deleted!',
                        //     text: response.message || 'Mission has been deleted.',
                        //     timer: 2000,
                        //     showConfirmButton: false,
                        //     background: '#101625',
                        //     color: '#ffffff'
                        // });
    
                        $('#missionRow-' + missionId).remove(); // Remove mission row
                        // getMissionStats();
                    },
                    error: function (xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
    
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            background: '#101625',
                            color: '#ffffff',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });
    

    


        // Handle Edit Mission Button Click
        $(document).on("click", ".edit-mission", function () {
            $(".cancel-btn").removeClass("d-none");
        
            const missionId = $(this).data("id");
            const row = $(`#missionRow-${missionId}`);
        
            // ✅ Get inspection type info
            const inspectionTypeEl = row.find("[data-name][data-inspectiontype-id]");
            const inspectionTypeId = inspectionTypeEl.data("inspectiontype-id");
            const inspectionTypeName = inspectionTypeEl.data("name");
        
            // ✅ Get mission date & note
            const missionDate = row.find(".mission_date").text().trim();
            const fullNote = row.find('.accordion-body .grayishtext[data-mission-note]').data('mission-note')?.trim() || '';

        
            // ✅ Get locations (names & IDs)
            const locationNames = row.find("[data-location-id]").text().split(',').map(loc => loc.trim().toLowerCase());
            const locationIds = row.find("[data-location-id]").data("location-id").toString().split(',');
        
            // ✅ Get pilot id
            const pilotId = row.data('pilot-id');
        
            // ✅ Get geo coords
            const latitude = row.find("[data-latitude]").data("latitude");
            const longitude = row.find("[data-longitude]").data("longitude");
        
            // ✅ Get region from data-region-id in the accordion's strong tag
            const regionId = row.find("[data-location-id]").data("region-id");
        
            // ✅ Fill the form
            $('#mission_date').val(missionDate);
            $('#note').val(fullNote);
        
            // inspection type radio
            $(`input[name="inspection_type"][value="${inspectionTypeId}"]`).prop("checked", true);
        
            // pilot dropdown
            if (pilotId) {
                $('#pilot_id').val(pilotId);
            }
        
            // lat / lng inputs
            $('#latitude').val(latitude ?? '');
            $('#longitude').val(longitude ?? '');
        
            // ✅ Set region and trigger location filter
            if (regionId) {
                $('#region_id').val(regionId).trigger('change');
        
                // Wait for region change filter to complete
                setTimeout(() => {
                    $('#location_id option').each(function () {
                        const locId = $(this).val();
                        $(this).prop("selected", locationIds.includes(locId));
                    });
                }, 150);
            }
        
            // ✅ Location checkboxes (if present)
            $(".location-checkbox").each(function () {
                const labelText = $(this).siblings("label").text().trim().toLowerCase();
                $(this).prop("checked", locationNames.includes(labelText));
            });
        
            // ✅ Set form into “edit” mode
            $("#addMissionForm").attr("data-mission-id", missionId);
            $(".form-title").text("Edit Mission");
            $(".mission-btn span").text("Update Mission");
            $(".mission-btn svg").attr({ "width": "30", "height": "30" });
        });
        
      
     

        
    
      
        function resetValues(){
            $("#addMissionForm")[0].reset();
        
            // ✅ Uncheck All Checkboxes
            $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
        
            // ✅ Restore Title & Button Text
            $(".form-title").text("Create New Mission");
            $(".mission-btn span").text("Create Mission");
        
            // ✅ Remove Cancel Button
          
            $(".mission-btn svg").attr({ "width": "16", "height": "16" });
            // ✅ Clear Mission ID
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
        }
        
        $(document).on("click", ".cancel-btn", function () {
            // ✅ Reset Form Fields
            resetValues();
        });
        
    

          // Submit Edit Mission Form via AJAX
    $("#editMissionForm").on("submit", function (e) {
        e.preventDefault();

        let formData = {
            mission_id: $("#edit_mission_id").val(),
            start_datetime: $("#edit_start_datetime").val(),
            end_datetime: $("#edit_end_datetime").val(),
            note: $("#edit_note").val(),
            inspection_types: $(".edit-inspection-type-checkbox:checked").map(function () { return this.value; }).get(),
            locations: $(".edit-location-checkbox:checked").map(function () { return this.value; }).get()
        };

        $.ajax({
            url: "/missions/update",
            type: "POST",
            data: formData,
            success: function (response) {
                console.log("Edit Mission Detail",response);
                if (!Array.isArray(response.allmails)) {
                    console.warn("Expected recipients to be an array but got:", recipients);
                }else{
                    console.log("array",response.allmails)
                }
                // sendMissionNotification({
                //     mission: response.mission,
                //     recipients: response.allmails,
                //     action: 'Updated'
                // });
                $("#editMissionModal").modal("hide");
                getRegionManagerMissions();
                // getMissionStats();
            },
            error: function (xhr) {
                alert("❌ Error updating mission: " + xhr.responseText);
            }
        });
    });
    function sendMissionNotification({ mission, recipients, action = 'created' }) {
        // ✅ Log recipients
        const realRecipients = recipients.map(r => r.email);
        console.log("Real email recipients:", realRecipients);
                const subject = `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}`;
                const content = `
            <p>Hello,</p>

            <p>A mission has been 
                <strong style="color:${action === 'deleted' ? 'red' : '#007bff'};">
                    ${action}
                </strong> by ${mission.created_by.name || 'N/A'} (${formatType(mission.created_by.type)}) in the dashboard.
                Please log in to your account to view the latest details.
            </p>

            <hr>

            <h3 style="margin-bottom: 5px;">📋 <u>Mission Details:</u></h3>
            <ul style="line-height: 1.6; padding-left: 20px;">
                <li><strong>Inspection Type:</strong> ${action === 'deleted' ? mission.inspection_type || 'N/A' : mission.inspection_type?.name || 'N/A'}</li>
                <li><strong>Mission Date:</strong> ${mission.mission_date || 'N/A'}</li>
                <li><strong>Region:</strong> ${mission.region_name || 'N/A'}</li>
                <li><strong>Location:</strong> ${mission.locations?.map(loc => loc.name).join(', ') || 'N/A'}</li>
                <li>
                    <strong style="font-size: 1.1em; color: #007bff;">Geo Location:</strong>
                    <ul style="margin-top: 4px; margin-bottom: 4px; padding-left: 18px;">
                        <li><span style="font-weight:600;">Latitude:</span> <span style="color:#333;">${mission.latitude || 'N/A'}</span></li>
                        <li><span style="font-weight:600;">Longitude:</span> <span style="color:#333;">${mission.longitude || 'N/A'}</span></li>
                    </ul>
                </li>
                ${
                    action === 'deleted'
                    ? `<li><strong>Deleted By:</strong> ${mission.deleted_by || 'N/A'}</li>
                    <li><strong>Deletion Reason:</strong> ${mission.deleted_reason || 'N/A'}</li>`
                    : ''
                }
            </ul>
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
            function formatType(type) {
                if (!type) return 'N/A';
                return type
                  .split('_')
                  .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                  .join(' ');
              }

    function sendApprovalNotification({ mission, recipients, decision,missioninfo }) {
            // Map user types to formatted strings
    const userTypeMap = {
        qss_admin: "QSS Admin",
        modon_admin: "Modon Admin",
        region_manager: "Region Manager",
        pilot: "Pilot",
        city_manager: "City Manager"
    };
    console.log("real recipients",recipients);
    // Get the formatted user type
    const formattedUserType = userTypeMap[mission.user_type] || "Unknown User";
        // Determine the action and email content based on the decision
        const action = decision == "approve" ? 'approved' : 'rejected';
        const subject = `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}`;
        //console.log("🚀 ~ file: missions.js:1 ~ sendApprovalNotification ~ missioninfo:", missioninfo)
      const content = `
    <p>Hello,</p>

     <p>A mission has been <strong style="color:${action === 'approved' ? 'green' : 'red'}">${action}</strong> by <strong>${formattedUserType}</strong> in the dashboard.
    Please log in to your account to view the latest details.</p>

    <hr>

    <h3 style="margin-bottom: 5px;">📋 <u>Mission Details:</u></h3>
    <ul style="line-height: 1.6;">
        <li><strong>Program:</strong> ${missioninfo.program || 'N/A'}</li>
        <li><strong>Region:</strong> ${missioninfo.location.region || 'N/A'}</li>
        <li><strong>City:</strong> ${missioninfo.location.city || 'N/A'}</li>
        <li><strong>Geolocation:</strong>
            <ul>
                <li><strong>Longitude:</strong> ${missioninfo.geoCoordinates.longitude || 'N/A'}</li>
                <li><strong>Latitude:</strong> ${missioninfo.geoCoordinates.latitude || 'N/A'}</li>
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
    
    // function sendMissionNotification(response, recipients) {
    //     // ✅ Log recipients
    //     console.log("Real email recipients:", recipients);
    
    //     // ✅ Add dummy recipients for testing
    //     // const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
    
    //     // ✅ Email content
    //     const subject = "New Mission Created";
    //     const content = `
    //         A new mission has been created in the dashboard. 
    //         Please log in to your account to view the latest details.
    
    //         Mission Details:
    //         - Inspection Type: ${response.mission.inspection_type.name}
    //         - Mission Date: ${response.mission_date}
    //         - Locations: ${response.mission.locations.map(loc => loc.name).join(', ')}
    
    //         Best regards,
    //         Admin Team
    //     `;
    
    //     // ✅ Show loading alert
    //     Swal.fire({
    //         title: 'Mission Created Successfully...',
    //         html: 'Please wait while emails are being sent..',
    //         allowOutsideClick: false,
    //         didOpen: () => {
    //             Swal.showLoading();
    //         }
    //     });
    
    //     // ✅ Send request
    //     fetch('/send-email', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         body: JSON.stringify({ recipients: recipients, subject, content })
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         Swal.fire({
    //             icon: 'success',
    //             title: 'Email Sent!',
    //             text: data.message || 'Notification email sent successfully.',
    //             timer: 2000,
    //             showConfirmButton: false
    //         });
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Email Error!',
    //             text: 'An error occurred while sending the email.',
    //         });
    //     });
    // }
 
    // $(document).on('click', '.downloadReportbtn', function(e) {
    //     e.preventDefault();
    
    //     // Fetch the information
    //     const missionOwner   = $("#viewOwnerInfo").text().trim();
    //     const pilot          = $("#viewpilotInfo").text().trim();
    //     const region         = $("#viewregionInfo").text().trim();
    //     const program        = $("#viewprogramInfo").text().trim();
    //     const location       = $("#viewlocationInfo").text().trim();
    //     const geoCoordinates = $("#viewgeoInfo").text().trim();
    //     const description    = $("#description").text().trim();
    
    //     // 📸 Fetch all image URLs inside #missionReportImages
    //     const images = [];
    //     $("#missionReportImages img.report-image").each(function() {
    //         const imgSrc = $(this).attr('src');
    //         if (imgSrc) {
    //             images.push(imgSrc);
    //         }
    //     });
    
    //     // Prepare data object
    //     const missionData = {
    //         owner: missionOwner,
    //         pilot: pilot,
    //         region: region,
    //         program: program,
    //         location: location,
    //         geo: geoCoordinates,
    //         description: description,
    //         images: images, // send images array too
    //     };
    
    //     console.log(missionData); // Just to see in console
    
    //     // Now send this to Laravel backend
    //     $.ajax({
    //         url: '/download-mission-pdf', // your Laravel route
    //         method: 'POST',
    //         data: JSON.stringify(missionData),
    //         xhrFields: {
    //             responseType: 'blob' // important for file download
    //         },
    //         contentType: 'application/json', // important for JSON sending
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
    //         },
    //         success: function(response, status, xhr) {
    //             const blob = new Blob([response], { type: 'application/pdf' });
    //             const link = document.createElement('a');
    //             link.href = window.URL.createObjectURL(blob);
    //             link.download = 'Mission_Report.pdf';
    //             link.click();
    //         },
    //         error: function(xhr) {
    //             console.error('PDF download failed');
    //         }
    //     });
    // });
    
    // $(document).on('click', '.downloadReportbtn', function(e) {
    //     e.preventDefault();
    
    //     // Fetch the information
    //     const missionOwner   = $("#viewOwnerInfo").text().trim();
    //     const pilot          = $("#viewpilotInfo").text().trim();
    //     const region         = $("#viewregionInfo").text().trim();
    //     const program        = $("#viewprogramInfo").text().trim();
    //     const location       = $("#viewlocationInfo").text().trim();
    //     const geoCoordinates = $("#viewgeoInfo").text().trim();
    //     const missiondate    = $("#viewmissionDateInfo").text().trim();
    //     const description    = $("#description").text().trim();
    
    //     // 📸 Fetch all image URLs inside #missionReportImages
    //     const images = [];
    //     $("#missionReportImages img.report-image").each(function() {
    //         const imgSrc = $(this).attr('src');
    //         if (imgSrc) {
    //             images.push(imgSrc);
    //         }
    //     });
    
    //     // Prepare data object
    //     const missionData = {
    //         owner: missionOwner,
    //         pilot: pilot,
    //         region: region,
    //         program: program,
    //         location: location,
    //         geo: geoCoordinates,
    //         description: description,
    //         images: images,
    //         missiondate:missiondate,
    //     };
    
    //     console.log(missionData);
    
    //     // 🚨 Show SweetAlert loading
    //     Swal.fire({
    //         title: 'Generating PDF...',
    //         text: 'Please wait while your report is being created.',
    //         allowOutsideClick: false,
    //         allowEscapeKey: false,
    //         didOpen: () => {
    //             Swal.showLoading();
    //         }
    //     });
    
    //     // Send data to backend
    //     $.ajax({
    //         url: '/download-mission-pdf',
    //         method: 'POST',
    //         data: JSON.stringify(missionData),
    //         xhrFields: {
    //             responseType: 'blob'
    //         },
    //         contentType: 'application/json',
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         success: function(response, status, xhr) {
    //             Swal.close(); // ✅ Close the loader on success
    
    //             const blob = new Blob([response], { type: 'application/pdf' });
    //             const link = document.createElement('a');
    //             link.href = window.URL.createObjectURL(blob);
    //             link.download = 'Mission_Report.pdf';
    //             link.click();
    //         },
    //         error: function(xhr) {
    //             Swal.close(); // Close loading
    //             Swal.fire('Failed!', 'PDF download failed. Please try again.', 'error');
    //             console.error('PDF download failed');
    //         }
    //     });
    // });
    
    // $(".search-icon-mission").click(function () {
    //     $(".search-input-mission").toggleClass("active").show().focus();
    // });

});
