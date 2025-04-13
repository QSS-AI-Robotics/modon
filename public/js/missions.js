$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    loadInspectionTypes();
    loadLocations();
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
    
  
    function loadLocations() {
        $.get('/missions/location-data', function (res) {
            const container = $('#locationsContainer');
            container.empty();
    
            res.locations.forEach(location => {
                container.append(`
                    <div class="form-check col-md-6 col-sm-12">
                        <input class="form-check-input location-checkbox" 
                               type="checkbox" 
                               name="locations[]" 
                               value="${location.id}" 
                               id="location_${location.id}">
                        <label class="form-check-label checkbox-text pe-2" 
                               for="location_${location.id}">
                            ${location.name}
                        </label>
                    </div>
                `);
            });
        });
    }
    




















    getRegionManagerMissions();
    getMissionStats();
    function getMissionStats() {
        $.ajax({
            url: "/missions/stats",
            type: "GET",
            success: function (response) {
                let totalMissions = response.total_missions || 0;
                let completedMissions = response.completed_missions || 0;
                let pendingMissions = totalMissions - completedMissions;
    
                // ✅ Avoid division by zero errors
                let completedPercentage = totalMissions > 0 ? (completedMissions / totalMissions) * 100 : 0;
                let pendingPercentage = totalMissions > 0 ? (pendingMissions / totalMissions) * 100 : 0;
    
                // ✅ Update Text
                $("#totalMissions").text(totalMissions);
                $("#completedMissions").text(completedMissions);
                $("#pendingMissions").text(pendingMissions);
    
                // ✅ Update Progress Bars
                $("#pendingMissionsBar").css("width", pendingPercentage + "%");
                $("#completedMissionsBar").css("width", completedPercentage + "%");
            },
            error: function (xhr) {
                console.error("❌ Error fetching mission stats:", xhr.responseText);
            }
        });
    }
    function getRegionManagerMissions() {
        $(".mission-btn svg").attr({ "width": "16", "height": "16" });
        $("#addMissionForm").removeAttr("data-mission-id");
        $(".cancel-btn").addClass("d-none");

        $.ajax({
            url: "/getmanagermissions", // Matches Laravel route for fetching missions
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty(); // Clear previous data
                
                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted">No missions available.</td>
                        </tr>
                    `);
                    return;
                }
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypesArray = mission.inspection_types.map(type => type.name);
                    let locationsArray = mission.locations.map(loc => loc.name);
    
                    // ✅ Bootstrap Dropdown for Inspection Types
                    let inspectionTypesHTML = `
                        <div class="dropdown d-flex align-items-center justify-content-center">
                            <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                ${inspectionTypesArray[0] || 'N/A'}...
                            </span>
                            <ul class="dropdown-menu text-center">
                                ${inspectionTypesArray.map(type => `<li class="dropdown-item">${type}</li>`).join("")}
                            </ul>
                        </div>

                        `;
    
                        let locationsHTML = `
                        <div class="dropdown d-flex align-items-center justify-content-center">
                            <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                ${locationsArray[0] || 'N/A'}...
                            </span>
                            <ul class="dropdown-menu text-center">
                                ${locationsArray.map(loc => `<li class="dropdown-item">${loc}</li>`).join("")}
                            </ul>
                        </div>
                    `;

                    // ✅ Mission Note (Only First 3 Words + Hover Dropdown)
                    let noteArray = mission.note ? mission.note.split(" ") : [];
                    let shortNote = noteArray.slice(0, 2).join(" ") || "No Notes";
                    let fullNote = noteArray.join(" ") || "No Notes Available";

                    let noteHTML = `
                    <div class="dropdown d-flex align-items-center justify-content-center w-100">
                        <button class="btn dropdown-toggle note-preview w-100 text-truncate" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            ${shortNote}...
                        </button>
                        <ul class="dropdown-menu text-start note-dropdown">
                            <li class="dropdown-item">${fullNote}</li>
                        </ul>
                    </div>
                `;
                

    
                    // ✅ Mission Status Display
                    let statusBadge = "";
                    if (mission.status === "Pending") {
                        statusBadge = `<span class="badge p-2 bg-danger">Pending</span>`;
                    } else if (mission.status === "In Progress") {
                        statusBadge = `<span class="badge p-2  bg-warning text-dark">In Progress</span>`;
                    } else if (mission.status === "Awaiting Report") {
                        statusBadge = `<span class="badge p-2 bg-primary">Awaiting Report</span>`;
                    } else if (mission.status === "Completed") {
                        statusBadge = `<span class="badge p-2  bg-success">Completed</span>`;
                    }
                    // ✅ Show Edit Button Only if Status is "Pending"
                    let editButton = mission.status === "Pending"
                    ? `<img src="./images/edit.png" alt="" class="edit-mission img-fluid actions" data-id="${mission.id}">`
                    : "";
                    let deleteButton = "";

                    if (mission.status === "Pending") {
                        deleteButton = `<img src="./images/delete.png" alt="" class="delete-mission img-fluid actions" data-id="${mission.id}">`;
                    } else if (mission.status === "Completed") {
                        deleteButton = `<img src="./images/view.png" alt="" class="view-mission-report img-fluid actions" data-id="${mission.id}">`;
                    } else {
                        // Show disabled delete icon (grayed out or with tooltip)
                        deleteButton = `<img src="./images/delete.png" alt="Delete Disabled" class="img-fluid actions disabled-delete" style="opacity: 0.5; cursor: not-allowed;" title="Only Pending missions can be deleted">`;
                    }
                    let row = `
                        <tr id="missionRow-${mission.id}">
                            <td>${inspectionTypesHTML}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td>${locationsHTML}</td>
                            <td class="align-middle">${noteHTML}</td>
                            <td>${statusBadge}</td>
                            
                            <td>                                          
                                ${editButton}
                                ${deleteButton}
                            </td>
                        </tr>
                    `;
    
                    $('#missionTableBody').append(row);
                });
            },
            error: function (xhr) {
                console.error("❌ Error fetching missions:", xhr.responseText);
                alert("Error fetching missions. Please try again.");
            }
        });
    }
    
    
    // Submit Add Mission Form via AJAX
    $('#addMissionForm').on('submit', function (e) {
        e.preventDefault();
    
        const $form = $(this);
        const $errorDiv = $('#mission-validation-errors');
        $errorDiv.addClass('d-none');
    
        const missionId = $form.attr("data-mission-id");
        const url = missionId ? "/missions/update" : "/missions/store";

    
        // ✅ Get selected checkboxes
        const selectedInspectionTypes = $('.inspection-type-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    
        const selectedLocations = $('.location-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    
        const formData = {
            mission_id: missionId,
            inspection_types: selectedInspectionTypes,
            start_datetime: $('#start_datetime').val(),
            end_datetime: $('#end_datetime').val(),
            note: $('#note').val(),
            locations: selectedLocations
        };
    
        // ✅ Frontend validation
        if (
            !formData.start_datetime ||
            !formData.end_datetime ||
            selectedInspectionTypes.length === 0 ||
            selectedLocations.length === 0
        ) {
            $errorDiv.removeClass('d-none').text("All fields are required.");

            return;
        }
        const buttonText = missionId ? "Updating..." : "Creating...";
    
        $(".mission-btn span").text(buttonText);
        $(".mission-btn svg").attr({ "width": "20", "height": "20" });
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
                $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
                $form.removeAttr("data-mission-id");
    
                $("h6").text("Create New Mission");
                $(".mission-btn span").text("New Mission");
    
                getRegionManagerMissions();
                getMissionStats();
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


    // Delete Mission

    $(document).on('click', '.delete-mission', function () {
        const missionId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This mission will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/missions/${missionId}`,
                    type: "DELETE",
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
                        getMissionStats();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.',
                            background: '#101625',
                            color: '#ffffff'
                        });
                    }
                });
            }
        });
    });
    
    // $(document).on('click', '.delete-mission', function () {
    //     let missionId = $(this).data('id');

    //     if (!confirm("Are you sure you want to delete this mission?")) return;

    //     $.ajax({
    //         url: `/missions/${missionId}`,
    //         type: "DELETE",
    //         success: function (response) {
    //             alert(response.message);
    //             $('#missionRow-' + missionId).remove(); // Remove row from table
    //             getMissionStats();
    //         },
    //         error: function (xhr) {
    //             alert("Error: " + xhr.responseText);
    //         }
    //     });
    // });

        // Handle Edit Mission Button Click
        $(document).on("click", ".edit-mission", function () {
            $(".cancel-btn").removeClass("d-none");
            let missionId = $(this).data("id"); // Get mission ID
            let row = $(`#missionRow-${missionId}`); // Get the row
        
            // Extract Data from Row
            let inspectionTypes = row.find("td:nth-child(1) .dropdown-menu li").map(function () {
                return $(this).text().trim();
            }).get();
        
            let startDatetime = row.find("td:nth-child(2)").text().trim();
            let endDatetime = row.find("td:nth-child(3)").text().trim();
            let locations = row.find("td:nth-child(4) .dropdown-menu li").map(function () {
                return $(this).text().trim();
            }).get();
            let note = row.find("td:nth-child(5) .dropdown-menu li").text().trim();
        
            // ✅ Update Form Fields
            $("#start_datetime").val(startDatetime);
            $("#end_datetime").val(endDatetime);
            $("#note").val(note);
        
            // ✅ Check Inspection Type Checkboxes
            $(".inspection-type-checkbox").each(function () {
                let typeValue = $(this).siblings("label").text().trim();
                $(this).prop("checked", inspectionTypes.includes(typeValue));
            });
        
            // ✅ Check Location Checkboxes
            $(".location-checkbox").each(function () {
                let locationValue = $(this).siblings("label").text().trim();
                $(this).prop("checked", locations.includes(locationValue));
            });
         
            // ✅ Update Title and Button Text
            $(".form-title").text("Edit Mission");
           // Show Cancel Button
            // Change Button Text
            $(".mission-btn svg").attr({ "width": "30", "height": "30" });
            $(".mission-btn span").text("Update Mission"); // Change Button Text
        

        

        
            // ✅ Store Mission ID for Updating
            $("#addMissionForm").attr("data-mission-id", missionId);
        });
        
        
        $(document).on("click", ".cancel-btn", function () {
            // ✅ Reset Form Fields
            $("#addMissionForm")[0].reset();
        
            // ✅ Uncheck All Checkboxes
            $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
        
            // ✅ Restore Title & Button Text
            $(".form-title").text("Create New Mission");
            $(".mission-btn span").text("New Mission");
        
            // ✅ Remove Cancel Button
          
            $(".mission-btn svg").attr({ "width": "16", "height": "16" });
            // ✅ Clear Mission ID
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
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
                getMissionStats();
            },
            error: function (xhr) {
                alert("❌ Error updating mission: " + xhr.responseText);
            }
        });
    });

});
