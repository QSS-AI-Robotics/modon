$(document).ready(function () {
    fetchMissions();
    fetchReports();

    function fetchMissions() {
        $.ajax({
            url: "/pilot/missions",
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty();
                $('#mission_id').empty().append('<option value="">Select a Mission</option>');

                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No new missions available.
                            </td>
                        </tr>
                    `);
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
                            <td>${locations}</td>
                        </tr>
                    `;

                    $('#missionTableBody').append(row);
                    $('#mission_id').append(`<option value="${mission.id}">${mission.id} - ${inspectionTypes}</option>`);
                });
            }
        });
    }

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

  // Handle Edit Report Button Click
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

    // Get existing images dynamically from the row using .imgPanel class
    let existingImages = "";
    reportRow.find('.imgPanel img').each(function () {
        let imgSrc = $(this).attr('src');
        existingImages += `
            <div class="image-container d-inline-block position-relative me-2">
                <img src="${imgSrc}" width="50">
                <button type="button" class="delete-existing-image btn btn-danger btn-sm position-absolute top-0 end-0" data-src="${imgSrc}">&times;</button>
            </div>
        `;
    });

    // Append existing images without replacing them
        $('#editImagePreview').html(existingImages);
    });

    // Handle Image Deletion from Preview (Existing Images)
    $(document).on('click', '.delete-existing-image', function () {
        $(this).closest('.image-container').remove();
    });

    // Handle New Image Preview (Appending New Images)
    let newImagesArray = []; // ✅ Track newly added images

    $('#edit_images').on('change', function () {
        let files = this.files;
        let previewContainer = $('#editImagePreview');
    
        Array.from(files).forEach((file, index) => {
            let fileIndex = newImagesArray.length; // Unique index
            newImagesArray.push(file); // ✅ Store new image
    
            let reader = new FileReader();
            reader.onload = function (e) {
                previewContainer.append(`
                    <div class="image-container d-inline-block position-relative me-2" data-index="${fileIndex}">
                        <img src="${e.target.result}" width="50">
                        <button type="button" class="delete-new-image btn btn-danger btn-sm position-absolute top-0 end-0">&times;</button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        });
    
        console.log("New Images Array After Adding:", newImagesArray); // ✅ Debugging
    });
    
    

    $(document).on('click', '.delete-new-image', function () {
        let imageContainer = $(this).closest('.image-container');
        let imageIndex = parseInt(imageContainer.attr('data-index'), 10); // ✅ Get image index
    
        // ✅ Remove from array
        if (imageIndex >= 0) {
            newImagesArray.splice(imageIndex, 1);
        }
    
        imageContainer.remove(); // ✅ Remove image preview
        console.log("Updated New Images Array After Deletion:", newImagesArray); // ✅ Debugging
    });
    



    // Handle Update Report Form Submission
    $('#editReportForm').on('submit', function (e) {
        e.preventDefault();
        $('#edit_images').val(''); 
        let reportId = $('#edit_report_id').val();
        let formData = new FormData(this);
    
        // ✅ Collect existing images from preview container
        let existingImages = [];
        $('#editImagePreview .image-container img').each(function () {
            let src = $(this).attr('src');
            if (!src.startsWith("data:image")) { // ✅ Only keep existing server images
                existingImages.push(src);
            }
        });
    
        formData.append('existing_images', JSON.stringify(existingImages));
    
        // ✅ Append only remaining new images
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
                alert(response.message);
                $('#editReportModal').modal('hide');
                fetchReports();
            },
            error: function (xhr) {
                alert("Error updating report: " + xhr.responseText);
            }
        });
    });
    
    
    

    // ✅ Delete Report
    $(document).on('click', '.delete-report', function () {
        let reportId = $(this).data('id');
    
        if (!confirm("Are you sure you want to delete this report?")) return;
    
        console.log("Deleting report ID:", reportId); // ✅ Debugging
    
        $.ajax({
            url: `/pilot/reports/${reportId}`,
            type: "POST", // ✅ DELETE method
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                alert(response.message);
                fetchReports();
            },
            error: function (xhr) {
                alert("Error deleting report: " + xhr.responseText);
            }
        });
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
                alert('Report submitted successfully!');
                $('#addReportModal').modal('hide');
                fetchReports();
            }
        });
    });
});
