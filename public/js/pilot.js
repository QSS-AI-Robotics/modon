$(document).ready(function () {
    fetchMissions();
    fetchReports();

    // ✅ Fetch Missions
    // function fetchMissions() {
    //     $.ajax({
    //         url: "/pilot/missions",
    //         type: "GET",
    //         success: function (response) {
    //             $('#missionTableBody').empty();
    //             $('#mission_id').empty().append('<option value="">Select a Mission</option>');
               
    //             if (response.missions.length === 0) {
    //                 $('#missionTableBody').append(`
    //                     <tr>
    //                         <td colspan="4" class="text-center text-muted">
    //                             No new missions available.
    //                         </td>
    //                     </tr>
    //                 `);
               
    //                 return;
    //             }

    //             $.each(response.missions, function (index, mission) {
    //                 let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
    //                 let locations = mission.locations.map(loc => loc.name).join("<br>");

    //                 let row = `
    //                     <tr>
    //                         <td>${inspectionTypes}</td>
    //                         <td>${mission.start_datetime}</td>
    //                         <td>${mission.end_datetime}</td>
    //                         <td>${mission.status}</td>
    //                         <td>${locations}</td>
    //                     </tr>
    //                 `;

    //                 $('#missionTableBody').append(row);
    //                 if (mission.report_submitted === 0) { 
    //                     $('#mission_id').append(`<option value="${mission.id}">${inspectionTypes}</option>`); 
    //                 }
                    
    //             });
               
    //         }
    //     });
    // }
    function fetchMissions() {
        $.ajax({
            url: "/pilot/missions",
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty();
                $('#mission_id').empty().append('<option value="">Select a Mission</option>');
                
                let allCompleted = true; // Assume all are completed until found otherwise
    
                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No new missions available.
                            </td>
                        </tr>
                    `);
                    $("#addReportBtn").prop("disabled", true);
                    return;
                }
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
                    let locations = mission.locations.map(loc => loc.name).join("<br>");
    
                    let row = `
                        <tr>
                            <td>${inspectionTypes}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td>${mission.status}</td>
                            <td>${locations}</td>
                        </tr>
                    `;
    
                    $('#missionTableBody').append(row);
    
                    if (mission.report_submitted === 0) { 
                        $('#mission_id').append(`<option value="${mission.id}">${inspectionTypes}</option>`); 
                    }
    
                    // Check if any mission is not completed
                    if (mission.status !== "Completed") {
                        allCompleted = false;
                    }
                });
    
                // Disable or enable the button based on mission status
                $("#addReportBtn").prop("disabled", allCompleted);
            }
        });
    }
    
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
        $('#addReportForm')[0].reset(); // Reset form fields
        $('#imagePreview').empty(); // Clear image preview if applicable
    });
    // ✅ Add Report
    $('#addReportForm').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "/pilot/reports/store",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                fetchReports();
                console.log('Report submitted successfully!');
                $('#addReportModal').modal('hide');
                fetchMissions();
            }
        });
    });
});
