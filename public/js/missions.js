$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    loadInspectionTypes();
   
    function loadInspectionTypes() {
        $.get('/missions/inspection-data', function (res) {
            const container = $('#inspectionTypesContainer');
            container.empty();
            res.inspectionTypes.forEach(type => {
                container.append(`
                    <div class="col-md-12 col-sm-12">
                        <div class="form-check"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-custom-class="custom-tooltip"
                            data-title="${type.description || ''}">
                            <input type="radio" class="form-check-input" name="inspection_type" value="${type.id}" id="inspection_${type.id}">
                            <label class="form-check-label checkbox-text" for="inspection_${type.id}">${type.name}</label>
                        </div>
                    </div>
                `);
            });
    
            // Enable tooltips
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
        });
    }

    
    




















    getRegionManagerMissions();
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
    function getRegionManagerMissions() {
        $(".mission-btn svg").attr({ "width": "16", "height": "16" });
        $("#addMissionForm").removeAttr("data-mission-id");
        $(".cancel-btn").addClass("d-none");

        $.ajax({
            url: "/getmanagermissions",
            type: "GET",
            success: function (response) {
                console.log("mission detal",response)
                $('#missionsAccordion').empty();
                $(".mission-details-wrapper").remove(); // remove any existing detail divs
    
                if (!response.missions.length) {
                    $('#missionsAccordion').append(`
                       <div class="col-12 text-center  my-4">No Missions Found For This Region</div>
                    `);
                    return;
                }
    
                $.each(response.missions, function (index, mission) {
                    const inspection = mission.inspection_types[0] || {};
                    const inspectionName = inspection.name || 'N/A';
                    const inspectionId = inspection.id || '';
    
                    const locations = mission.locations.map(loc => loc.name).join(', ') || 'N/A';
    
                    const noteWords = mission.note ? mission.note.split(" ") : [];
                    const shortNote = noteWords.slice(0, 2).join(" ");
                    const fullNote = mission.note || "No Notes";
    
             
    
                    let statusBadge = "";
                    switch (mission.status) {
                        case "Pending":
                            statusBadge = `<span class="badge p-2 bg-danger">Pending</span>`; break;
                        case "Rejected":
                            statusBadge = `<span class="badge p-2 bg-warning">Rejected</span>`; break;
                        case "In Progress":
                            statusBadge = `<span class="badge p-2 bg-info text-dark">In Progress</span>`; break;
                        case "Awaiting Report":
                            statusBadge = `<span class="badge p-2 bg-primary">Awaiting Report</span>`; break;
                        case "Completed":
                            statusBadge = `<span class="badge p-2 bg-success">Completed</span>`; break;
                    }
    
                    const editButton = mission.status === "Pending"
                        ? `<img src="./images/edit.png" alt="Edit" class="edit-mission img-fluid actions" data-id="${mission.id}">`
                        : "";
    
                    const deleteButton = mission.status === "Pending"
                        ? `<img src="./images/delete.png" alt="Delete" class="delete-mission img-fluid actions" data-id="${mission.id}">`
                        : mission.status === "Completed"
                            ? `<img src="./images/view.png" alt="View" class="view-mission-report img-fluid actions" data-id="${mission.id}">`
                            : `<img src="./images/delete.png" alt="Delete Disabled" class="view-mission img-fluid actions disabled-delete" style="opacity: 0.5; cursor: not-allowed;" title="Only Pending missions can be deleted">`;
    
                            const getStatusBadge = (value) => {
                                switch (value) {
                                    case 1:
                                        return `<strong class="text-success">Approved</strong>`;
                                    case 2:
                                        return `<strong class="text-danger">Rejected</strong>`;
                                    default:
                                        return `<strong class="text-warning">Pending</strong>`;
                                }
                            };
                            
                            const modonManagerStatus = getStatusBadge(mission.approval_status?.modon_manager_approved);
                            const regionManagerStatus = getStatusBadge(mission.approval_status?.region_manager_approved);
                            const cityManagerStatus = getStatusBadge(mission.approval_status?.city_manager_approved);
                    const row = `
                        <div class="accordion-item " id="missionRow-${mission.id}"   data-pilot-id="${mission.pilot_id}">
                            <h2 class="accordion-header" id="heading-${mission.id}">
                                <button class="accordion-button collapsed d-flex px-3 py-2 " type="button">
                                    <div class="row w-100 justify-content-between label-text">
                                        <div class="col-3 " data-name="${inspectionName}" data-inspectiontype-id="${inspectionId}">${inspectionName}</div>
                                        <div class="col-2 text-center mission_date">${mission.mission_date}</div>
                                        <div class="col-3 text-center">${locations}</div>
                                        <div class="col-2 text-center">${statusBadge}</div>
                                    
                                        <div class="col-2 text-center">
                                        ${editButton}
                                        ${deleteButton}
                                            <img src="./images/view.png" alt="View" class=" view-mission img-fluid actions toggle-details" data-id="${mission.id}"  data-bs-toggle="collapse" data-bs-target="#collapse-${mission.id}" aria-expanded="false" aria-controls="collapse-${mission.id}">
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-${mission.id}" class="accordion-collapse collapse  " aria-labelledby="heading-${mission.id}" data-bs-parent="#missionsAccordion">
                                <div class="accordion-body px-4 py-2 label-text">
                                    <strong>Program:</strong> ${inspectionName}<br>
                                    
                                    <strong>Mission Date:</strong> ${mission.mission_date}<br>
                                    <strong>Locations:</strong> ${locations}<br>
                                    <strong data-pilot-id="${mission.pilot_info.id}">Pilot Name:</strong> ${mission.pilot_info.name}<br>

                                    <strong>Note:</strong class="mission_note"> ${fullNote}<br><br>
                                    <div class="d-flex ">
                                       <div class="row w-100   align-items-center">
                                            <strong>Mission Approval</strong><br>
                                              <div class="col-3 label-text">
                                                    <p>Modon Manager: ${modonManagerStatus}</p>
                                              </div>
                                              <div class="col-3 label-text">
                                                    <p>Region Manager: ${regionManagerStatus}</p>
                                              </div>
                                              <div class="col-3 label-text">
                                                    <p>City Manager: ${cityManagerStatus}</p>
                                              </div>
                                             
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#missionsAccordion').append(row);
                });
            },
            error: function (xhr) {
                console.error("❌ Error fetching missions:", xhr.responseText);
                alert("Error fetching missions. Please try again.");
            }
        });
    }
    
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
    const checkForPilot = $('input[name="pilot_id"]').val();
    if (!checkForPilot) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'No pilot assigned to this region. Please contact Modon Authority.',
            background: '#101625',
            color: '#ffffff'
        });
        return;
    }
    const $form = $(this);
    const $errorDiv = $('#mission-validation-errors').addClass('d-none');
    
    const missionId = $form.attr("data-mission-id");
    const url = missionId ? "/missions/update" : "/missions/store";

    // ✅ Single inspection_type (radio)
    const inspectionType = $('input[name="inspection_type"]:checked').val();


    const locationId = $('#location_id').data('location-id');
    const selectedLocations = locationId ? [locationId] : [];
    const pilotId = $('#pilot_id').val();
    const formData = {
        mission_id: missionId,
        inspection_type: inspectionType,
        mission_date: $('#mission_date').val(),
        note: $('#note').val(),
        locations: selectedLocations,
        pilot_id: pilotId
    };
    
    if (!formData.mission_date || !inspectionType || !pilotId || selectedLocations.length === 0) {
        $errorDiv.removeClass('d-none').text("All fields are required.");
        return;
    }

    console.log(selectedLocations)
    // UI feedback
    const buttonText = missionId ? "Updating..." : "Creating...";
    $(".mission-btn span").text(buttonText);
    $(".mission-btn svg").attr({ "width": "20", "height": "20" });
    // console.table(formData);

    // ✅ Send AJAX
    $.ajax({
        url: url,
        type: "POST",
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
            inputLabel: 'Reason for deletion (required for region managers)',
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Mission has been deleted.',
                            timer: 2000,
                            showConfirmButton: false,
                            background: '#101625',
                            color: '#ffffff'
                        });
    
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
    
    //     $(document).on('click', '.delete-mission', function () {
    //     const missionId = $(this).data('id');
    //     resetValues();
    //     Swal.fire({
    //         title: 'Are you sure?',
    //         text: "This mission will be permanently deleted.",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#d33',
    //         cancelButtonColor: '#6c757d',
    //         confirmButtonText: 'Yes, delete it!',
    //         cancelButtonText: 'Cancel'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 url: `/missions/${missionId}`,
    //                 type: "DELETE",
    //                 success: function (response) {
    //                     Swal.fire({
    //                         icon: 'success',
    //                         title: 'Deleted!',
    //                         text: response.message || 'Mission has been deleted.',
    //                         timer: 2000,
    //                         showConfirmButton: false,
    //                         background: '#101625',
    //                         color: '#ffffff'
    //                     });
    
    //                     $('#missionRow-' + missionId).remove(); // Remove mission row
    //                     // getMissionStats();
    //                 },
    //                 error: function (xhr) {
    //                     const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
    
    //                     Swal.fire({
    //                         icon: 'error',
    //                         title: 'Error!',
    //                         text: errorMessage,
    //                         background: '#101625',
    //                         color: '#ffffff',
    //                         confirmButtonColor: '#d33'
    //                     });
    //                 }
    //             });
    //         }
    //     });
    // });
    
    


        // Handle Edit Mission Button Click
        $(document).on("click", ".edit-mission", function () {
            $(".cancel-btn").removeClass("d-none");
        
            const missionId = $(this).data("id");
            const row = $(`#missionRow-${missionId}`);
        
            // ✅ Get inspection type info from the header div
            const inspectionTypeEl = row.find("[data-name][data-inspectiontype-id]");
            const inspectionTypeId = inspectionTypeEl.data("inspectiontype-id");
            const inspectionTypeName = inspectionTypeEl.data("name");
        
            // ✅ Get mission date, note, and locations
            const missionDate = row.find(".mission_date").text().trim();
        
            // Find full note from accordion-body (not the short version shown in header)
            const fullNote = row.find(".accordion-body").text().match(/Note:\s*(.*)/i)?.[1]?.trim() || '';
        
            const locationsText = row.find(".accordion-button .col-3").eq(1).text(); // second .col-3 (location)
            const locationNames = locationsText.split(',').map(loc => loc.trim().toLowerCase());
        
            // ✅ Get assigned pilot ID (from hidden input or attribute)
            const pilotId = row.data('pilot-id'); // requires pilot_id to be stored as data attribute
        
            // ✅ Fill the form fields
            $('#mission_date').val(missionDate);
            $('#note').val(fullNote);
        
            // ✅ Select the correct inspection type radio button
            $(`input[name="inspection_type"][value="${inspectionTypeId}"]`).prop("checked", true);
        
            // ✅ Select the correct pilot in the dropdown
            if (pilotId) {
                $('#pilot_id').val(pilotId);
            }
        
            // ✅ Check checkboxes for locations
            $(".location-checkbox").each(function () {
                const labelText = $(this).siblings("label").text().trim().toLowerCase();
                $(this).prop("checked", locationNames.includes(labelText));
            });
        
            // ✅ Mark mission ID for update
            $("#addMissionForm").attr("data-mission-id", missionId);
        
            // ✅ Update UI
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
                alert(response.message);
                $("#editMissionModal").modal("hide");
                getRegionManagerMissions();
                // getMissionStats();
            },
            error: function (xhr) {
                alert("❌ Error updating mission: " + xhr.responseText);
            }
        });
    });

});
