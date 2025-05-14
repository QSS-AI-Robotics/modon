// Define translations for backend validation and exception errors
const backendErrorTranslations = {
    en: {
        inspection_type_required: "Inspection type is required.",
        inspection_type_exists: "The selected inspection type does not exist.",
        mission_date_required: "Mission date is required.",
        mission_date_invalid: "Mission date must be a valid date.",
        mission_date_future: "Mission date must be today or a future date.",
        note_invalid: "Note must be a valid string.",
        locations_required: "At least one location is required.",
        locations_exists: "One or more selected locations do not exist.",
        pilot_id_required: "Pilot is required.",
        pilot_id_exists: "The selected pilot does not exist.",
        latitude_required: "Latitude is required.",
        latitude_invalid: "Latitude must be a number between -90 and 90.",
        longitude_required: "Longitude is required.",
        longitude_invalid: "Longitude must be a number between -180 and 180.",
        region_id_required: "Region is required.",
        region_id_exists: "The selected region does not exist.",
        mission_creation_failed: "Failed to create mission.",
        something_went_wrong: "Something went wrong.",
        server_error: "Error!",
        unauthorized_delete: "You are not authorized to delete this mission.",
        mission_already_approved: "This mission has already been approved. Only the region manager or modon admin can delete it.",
        delete_reason_required: "Please provide a reason for deleting this mission.",
        something_went_wrong: "Something went wrong.",
    },
    ar: {
        inspection_type_required: "نوع التفتيش مطلوب.",
        inspection_type_exists: "نوع التفتيش المحدد غير موجود.",
        mission_date_required: "تاريخ المهمة مطلوب.",
        mission_date_invalid: "يجب أن يكون تاريخ المهمة تاريخًا صالحًا.",
        mission_date_future: "يجب أن يكون تاريخ المهمة اليوم أو تاريخًا مستقبليًا.",
        note_invalid: "يجب أن تكون الملاحظة نصًا صالحًا.",
        locations_required: "مطلوب تحديد موقع واحد على الأقل.",
        locations_exists: "واحد أو أكثر من المواقع المحددة غير موجود.",
        pilot_id_required: "الطيار مطلوب.",
        pilot_id_exists: "الطيار المحدد غير موجود.",
        latitude_required: "خط العرض مطلوب.",
        latitude_invalid: "يجب أن يكون خط العرض رقمًا بين -90 و 90.",
        longitude_required: "خط الطول مطلوب.",
        longitude_invalid: "يجب أن يكون خط الطول رقمًا بين -180 و 180.",
        region_id_required: "المنطقة مطلوبة.",
        region_id_exists: "المنطقة المحددة غير موجودة.",
        mission_creation_failed: "فشل في إنشاء المهمة.",
        something_went_wrong: "حدث خطأ ما.",
         server_error: "خطأ!",
        unauthorized_delete: "غير مصرح لك بحذف هذه المهمة.",
        mission_already_approved: "تمت الموافقة على هذه المهمة بالفعل. يمكن فقط لمدير المنطقة أو مدير مدن حذفها.",
        delete_reason_required: "يرجى تقديم سبب لحذف هذه المهمة.",
        something_went_wrong: "حدث خطأ ما."
    }
};
  function normalizeDescription(desc) {
    return desc.replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g,"").trim().toLowerCase();
}
   function toLangKey(str) {
        return str
            .trim()
            .replace(/\b3\b/g, 'three')      // Replace standalone digit 3 with 'three'
            .replace(/3/g, 'three')          // Replace any 3 with 'three'
            .replace(/&/g, 'and')            // Replace & with and
            .replace(/\s+/g, '_');           // Replace spaces with underscores
    }

// Function to translate backend error keys
function translateBackendError(errorKey) {

    // Get the selected language from localStorage
const selectedLang = localStorage.getItem("selectedLang") || "en";

// Use the selected language for backend error messages
const backendLang = backendErrorTranslations[selectedLang] || backendErrorTranslations.en;
    return backendLang[errorKey] || backendLang.something_went_wrong;
}
function loadInspectionTypes(selectedLang) {
    // Define translations for descriptions
    const descriptionTranslations = {
        en: {
            "Detecting violations in the external yards of factories": "Detecting violations in the external yards of factories",
            "Security inspection of hard-to-reach areas": "Security inspection of hard-to-reach areas",
            "Update photos of the industrial city and cover events and activities": "Update photos of the industrial city and cover events and activities",
            "Monitoring road safety and detecting damages in industrial cities": "Monitoring road safety and detecting damages in industrial cities",
            "Imaging and analyzing harmful gases and emissions and their levels in industrial cities": "Imaging and analyzing harmful gases and emissions and their levels in industrial cities",
            "Preparing a 3D map of the industrial city": "Preparing a 3D map of the industrial city",
            "Responding to emergency cases reported to the specialized emergency call center": "Responding to emergency cases reported to the specialized emergency call center"
        },
        ar: {
            "Detecting violations in the external yards of factories": "رصد المخالفات في الساحات الخارجية للمصانع",
            "Security inspection of hard-to-reach areas": "التفتيش الأمني على المناطق التي يصعب الوصول إليها.",
            "Update photos of the industrial city and cover events and activities": "تحديث صور المدينة الصناعية وتغطية الأحداث والأنشطة.",
            "Monitoring road safety and detecting damages in industrial cities": "مراقبة سلامة الطرق ورصد الأضرار في المدن الصناعية.",
            "Imaging and analyzing harmful gases and emissions and their levels in industrial cities": "تصوير وتحليل الغازات الضارة والانبعاثات ومستوياتها في المدن الصناعية.",
            "Preparing a 3D map of the industrial city": "إعداد خريطة ثلاثية الأبعاد للمدينة الصناعية.",
            "Responding to emergency cases reported to the specialized emergency call center": "الاستجابة للحالات الطارئة المُبلّغ عنها إلى مركز الطوارئ المتخصص."
        }
    };

    // Fetch inspection data
    $.get('/missions/inspection-data', function (res) {
        const container = $('#inspectionTypesContainer');
        container.empty();

        const translations = descriptionTranslations[selectedLang] || descriptionTranslations.en;

        res.inspectionTypes.forEach(type => {
            const langKey = toLangKey(type.name);

            const normalizedDescription = normalizeDescription(type.description);
            const translatedDescription = Object.keys(translations).find(key =>
                normalizeDescription(key) === normalizedDescription
            );

            const finalDescription = translatedDescription ? translations[translatedDescription] : type.description;

            container.append(`
                <div class="col-md-12 col-sm-12">
                    <div class="form-check"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        data-bs-custom-class="custom-tooltip"
                        data-title="${finalDescription.replace(/&nbsp;/g, '').trim()}">
                        <input type="radio" class="form-check-input" name="inspection_type" value="${type.id}" id="inspection_${type.id}">
                        <label class="form-check-label checkbox-text" data-lang-key="${langKey}" for="inspection_${type.id}">${type.name}</label>
                    </div>
                </div>
            `);
        });

        // Enable tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => {
            const content = el.getAttribute('data-title');
            const tooltipTitle = selectedLang === "ar"
                ? `<strong class="text-dark"><span data-lang-key="missionDescription">وصف المهمة:</span></strong><br>${content}`
                : `<strong class="text-dark"><span data-lang-key="missionDescription">Mission Description:</span></strong><br>${content}`;
            new bootstrap.Tooltip(el, {
                html: true,
                title: tooltipTitle,
                customClass: 'custom-tooltip',
                trigger: 'hover focus'
            });
        });

        // Translate the container
        updateLanguageTexts(selectedLang);
    });
}
$(document).ready(function () {
    // CSRF Token Setup for AJAX
    const rolesToDisable = ['modon_admin', 'region_manager', 'general_manager','qss_admin'];
    const userRole =  $('#userTypeFront').attr('data-lang-key');
    if (rolesToDisable.includes(userRole)) {
        $('#CreateMissionBtn').prop('disabled', true);
    }
    function toLangKey(str) {
        return str
            .trim()
            .replace(/\b3\b/g, 'three')      // Replace standalone digit 3 with 'three'
            .replace(/3/g, 'three')          // Replace any 3 with 'three'
            .replace(/&/g, 'and')            // Replace & with and
            .replace(/\s+/g, '_');           // Replace spaces with underscores
    }

    function formatCityNames(text) {
        return text.trim().replace(/\s+/g, '_');
    }
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
    const selectedLang = localStorage.getItem("selectedLang") || "en";
    loadInspectionTypes(selectedLang);

   
 
    // if (typeof updateLanguageTexts === "function") {
    //    console.log("now its time to update the language");
    //     updateLanguageTexts(currentLang);
    // }
       // Utility function to generate lang key
    //    function toLangKey(str) {
    //     return str
    //         .trim()
    //         .replace(/\b3\b/g, 'three')      // Replace standalone digit 3 with 'three'
    //         .replace(/3/g, 'three')          // Replace any 3 with 'three'
    //         .replace(/&/g, 'and')            // Replace & with and
    //         .replace(/\s+/g, '_');           // Replace spaces with underscores
    // }




//     function normalizeDescription(desc) {
//     return desc.replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g,"").trim().toLowerCase();
// }
function loadInspectionTypesOld() {
    // Define translations for descriptions
    const descriptionTranslations = {
        en: {
            "Detecting violations in the external yards of factories": "Detecting violations in the external yards of factories",
            "Security inspection of hard-to-reach areas": "Security inspection of hard-to-reach areas",
            "Update photos of the industrial city and cover events and activities": "Update photos of the industrial city and cover events and activities",
            "Monitoring road safety and detecting damages in industrial cities": "Monitoring road safety and detecting damages in industrial cities",
            "Imaging and analyzing harmful gases and emissions and their levels in industrial cities": "Imaging and analyzing harmful gases and emissions and their levels in industrial cities",
            "Preparing a 3D map of the industrial city": "Preparing a 3D map of the industrial city",
            "Responding to emergency cases reported to the specialized emergency call center": "Responding to emergency cases reported to the specialized emergency call center"
        },
        ar: {
            "Detecting violations in the external yards of factories": "رصد المخالفات في الساحات الخارجية للمصانع",
            "Security inspection of hard-to-reach areas": "التفتيش الأمني على المناطق التي يصعب الوصول إليها.",
            "Update photos of the industrial city and cover events and activities": "تحديث صور المدينة الصناعية وتغطية الأحداث والأنشطة.",
            "Monitoring road safety and detecting damages in industrial cities": "مراقبة سلامة الطرق ورصد الأضرار في المدن الصناعية.",
            "Imaging and analyzing harmful gases and emissions and their levels in industrial cities": "تصوير وتحليل الغازات الضارة والانبعاثات ومستوياتها في المدن الصناعية.",
            "Preparing a 3D map of the industrial city": "إعداد خريطة ثلاثية الأبعاد للمدينة الصناعية.",
            "Responding to emergency cases reported to the specialized emergency call center": "الاستجابة للحالات الطارئة المبلغ عنها إلى مركز الاتصال الطارئ المتخصص."
        }
    };

    $.get('/missions/inspection-data', function (res) {
        const container = $('#inspectionTypesContainer');
        container.empty();

        const selectedLang = localStorage.getItem("selectedLang") || "en"; // Get selected language
        const translations = descriptionTranslations[selectedLang] || descriptionTranslations.en;
        
res.inspectionTypes.forEach(type => {
    const langKey = toLangKey(type.name);

    const normalizedDescription = normalizeDescription(type.description);
    const translatedDescription = Object.keys(translations).find(key => 
        normalizeDescription(key) === normalizedDescription
    );

    const finalDescription = translatedDescription ? translations[translatedDescription] : type.description;

    container.append(`
        <div class="col-md-12 col-sm-12">
            <div class="form-check"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                data-bs-custom-class="custom-tooltip"
                data-title="${finalDescription.replace(/&nbsp;/g,'').trim()}">
                <input type="radio" class="form-check-input" name="inspection_type" value="${type.id}" id="inspection_${type.id}">
                <label class="form-check-label checkbox-text" data-lang-key="${langKey}" for="inspection_${type.id}">${type.name}</label>
            </div>
        </div>
    `);
});

        // Enable tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => {
            const content = el.getAttribute('data-title');
            const tooltipTitle = selectedLang === "ar" 
                ? `<strong class="text-dark"><span data-lang-key="missionDescription">وصف المهمة:</span></strong><br>${content}`
                : `<strong class="text-dark"><span data-lang-key="missionDescription">Mission Description:</span></strong><br>${content}`;
            new bootstrap.Tooltip(el, {
                html: true,
                title: tooltipTitle,
                customClass: 'custom-tooltip',
                trigger: 'hover focus'
            });
        });

        // Translate the container
        let currentLang = localStorage.getItem("selectedLang") || "ar";
        updateLanguageTexts(currentLang);
    });
}

    
(function() {
    const originalSetItem = localStorage.setItem;
    localStorage.setItem = function(key, value) {
        originalSetItem.apply(this, arguments);
        window.dispatchEvent(new CustomEvent("localStorageModified", {
            detail: { key, value }
        }));
    };
})();

window.addEventListener("localStorageModified", function(e) {
    if (e.detail.key === "selectedLang") {
        let regionid = document.getElementById('region_id').value;
        console.log("Selected region ID:", regionid);
        filterLocationsByRegionId(regionid);
        console.log("Detected selectedLang change:", e.detail.value);
        updateLanguageTexts(e.detail.value);
    }
});
function filterLocationsByRegionId(regionId) {
    const locationSelect = document.getElementById('location_id');
    if (!locationSelect) return;

    // Clone all original options on first call and store in DOM for reuse
    if (!locationSelect.dataset.originalOptionsStored) {
        const allOptions = Array.from(locationSelect.options).map(opt => opt.cloneNode(true));
        locationSelect.dataset.originalOptionsStored = 'true';
        locationSelect.dataset.allOptions = JSON.stringify(allOptions.map(opt => ({
            value: opt.value,
            "data-lang-key": opt.getAttribute('data-lang-key') || '',
            text: opt.text,
            regionId: opt.getAttribute('data-region-id') || '',
            selected: opt.selected
        })));
    }

    const allOptions = JSON.parse(locationSelect.dataset.allOptions);

    // Clear current options
    locationSelect.innerHTML = '';

    let hasMatch = false;
    allOptions.forEach(opt => {
        if (opt.regionId === regionId) {
            const newOption = document.createElement('option');
            newOption.value = opt.value;
            newOption.text = opt.text;
            newOption.setAttribute('data-region-id', opt.regionId);
            newOption.setAttribute('data-lang-key', opt["data-lang-key"]);
            locationSelect.appendChild(newOption);
            hasMatch = true;
        }
    });

    locationSelect.disabled = !hasMatch;

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
        // Mission Date
    const missionDate = $container.find('span[data-mission-date]').data('mission-date');
    
      
    // Construct JSON object
    const missionData = {
        missionId: missionId,
        decision: decision,
        missionDate: missionDate,
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
    // Define translations for SweetAlert
    const swalTranslations = {
        en: {
            approve_title: "Approve Mission?",
            approve_text: "Are you sure you want to approve this mission?",
            reject_title: "Reject Mission?",
            reject_text: "Are you sure you want to reject this mission?",
            rejection_reason_title: "Rejection Reason",
            rejection_reason_label: "Please explain why you’re rejecting this mission",
            rejection_reason_placeholder: "Enter reason here...",
            rejection_reason_required: "Rejection reason is required!",
            confirm_button: "Yes, Approve",
            reject_button: "Yes, Reject",
            cancel_button: "Cancel"
        },
        ar: {
            approve_title: "الموافقة على المهمة؟",
            approve_text: "هل أنت متأكد أنك تريد الموافقة على هذه المهمة؟",
            reject_title: "رفض المهمة؟",
            reject_text: "هل أنت متأكد أنك تريد رفض هذه المهمة؟",
            rejection_reason_title: "سبب الرفض",
            rejection_reason_label: "يرجى توضيح سبب رفض هذه المهمة",
            rejection_reason_placeholder: "أدخل السبب هنا...",
            rejection_reason_required: "سبب الرفض مطلوب!",
            confirm_button: "نعم، موافقة",
            reject_button: "نعم، رفض",
            cancel_button: "إلغاء"
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert translations
    const swalLang = swalTranslations[selectedLang] || swalTranslations.en;


        // const isApproval = decision === 'approve';
        // const actionText = isApproval ? 'Approve' : 'Reject';
        // const confirmButtonColor = isApproval ? '#28a745' : '#dc3545';

        const isApproval = decision === 'approve';
    const actionText = isApproval ? swalLang.approve_title : swalLang.reject_title;
    const actionTextBody = isApproval ? swalLang.approve_text : swalLang.reject_text;
    const confirmButtonText = isApproval ? swalLang.confirm_button : swalLang.reject_button;
    const confirmButtonColor = isApproval ? '#28a745' : '#dc3545';
    Swal.fire({
        title: actionText,
        text: actionTextBody,
        icon: isApproval ? 'success' : 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmButtonText,
        cancelButtonText: swalLang.cancel_button
    }).then((result) => {
        if (!result.isConfirmed) return;

        // If approving, go straight to AJAX
        if (isApproval) {
            submitApproval(missionId, decision, null, missionData);
        } else {
            // If rejecting, ask for reason
            Swal.fire({
                title: swalLang.rejection_reason_title,
                input: 'textarea',
                inputLabel: swalLang.rejection_reason_label,
                inputPlaceholder: swalLang.rejection_reason_placeholder,
                inputAttributes: {
                    'aria-label': swalLang.rejection_reason_label
                },
                inputValidator: (value) => {
                    if (!value) {
                        return swalLang.rejection_reason_required;
                    }
                },
                showCancelButton: true,
                confirmButtonText: swalLang.reject_button,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                cancelButtonText: swalLang.cancel_button
            }).then((rejectResult) => {
                if (rejectResult.isConfirmed) {
                    submitApproval(missionId, decision, rejectResult.value, missionData);
                }
            });
        }
    });
        // Swal.fire({
        //     title: `${actionText} Mission?`,
        //     text: `Are you sure you want to ${actionText.toLowerCase()} this mission?`,
        //     icon: isApproval ? 'success' : 'warning',
        //     showCancelButton: true,
        //     confirmButtonColor: confirmButtonColor,
        //     cancelButtonColor: '#6c757d',
        //     confirmButtonText: `Yes, ${actionText}`,
        // }).then((result) => {
        //     if (!result.isConfirmed) return;
    
        //     // ✅ If approving, go straight to AJAX
        //     if (isApproval) {
        //         //alert("🚀 Mission Data:");
        //         submitApproval(missionId, decision,null, missionData);

        //     } else {
        //         // ❌ If rejecting, ask for reason
        //         Swal.fire({
        //             title: 'Rejection Reason',
        //             input: 'textarea',
        //             inputLabel: 'Please explain why you’re rejecting this mission',
        //             inputPlaceholder: 'Enter reason here...',
        //             inputAttributes: {
        //                 'aria-label': 'Rejection reason'
        //             },
        //             inputValidator: (value) => {
        //                 if (!value) {
        //                     return 'Rejection reason is required!';
        //                 }
        //             },
        //             showCancelButton: true,
        //             confirmButtonText: 'Submit Rejection',
        //             confirmButtonColor: '#dc3545',
        //             cancelButtonColor: '#6c757d',
        //         }).then((rejectResult) => {
        //             if (rejectResult.isConfirmed) {
        //                 submitApproval(missionId, decision, rejectResult.value,missionData);
        //             }
        //         });
        //     }
        // });
    });
    


 // ✅  Function to submit approval or rejection with optional note
 function submitApproval(missionId, decision, rejectionNote = null, mission_info=null) {
    // Define translations for SweetAlert
    const swalTranslations = {
        en: {
            success_title: "Success",
            success_message: "Decision updated!",
            error_title: "Error",
            error_message: "Something went wrong",
        },
        ar: {
            success_title: "نجاح",
            success_message: "تم تحديث القرار!",
            error_title: "خطأ",
            error_message: "حدث خطأ ما",
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert translations
    const swalLang = swalTranslations[selectedLang] || swalTranslations.en;

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

  

            // Log the initial response for debugging
            console.log("🚀 Initial Response:", response);

            let recipients = [...new Set(response.allmails.map(user => user.email))];

            console.log("Now ",response.user_type)
            // Call the new sendApprovalNotification function
            sendApprovalNotification({
                mission: response,
                recipients: recipients,
                decision: decision,
                missioninfo: mission_info
            });

        //     Swal.fire('Success', response.message || 'Decision updated!', 'success');
        //     getRegionManagerMissions();
        // },
        // error: function (xhr) {
        //     Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
        // }
        Swal.fire({
                icon: 'success',
                title: swalLang.success_title,
                text: swalLang.success_message,
            });

            getRegionManagerMissions();
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: swalLang.error_title,
                text: swalLang.error_message,
            });
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

        const status = $(this).data("lang-key");// get text like "pending"
        const date = $("#filterMission").val(); // get date if selected

        getRegionManagerMissions({ status, date });
    });
    $("#filterMission").on("change", function () {
        const date = $(this).val();
        const status = $(".mstatus.activeStatus").text().trim().toLowerCase();
        Swal.fire({
            title: 'Loading Missions...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        getRegionManagerMissions({ status, date });

    
        Swal.close();
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
                        <div class="col-12 text-center my-4" data-lang-key="noMissionsFound">No Missions Found</div>
                    `);
                    let currentLang = localStorage.getItem("selectedLang") || "ar";
                    //console.log('Calling updateLanguageTexts with:', currentLang);
                    updateLanguageTexts(currentLang);
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
                        case "Approved":        statusBadge = `<span class="badge p-2 bg-success d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="approved">Approved</span>`; break;
                        case "Pending":         statusBadge = `<span class="badge p-2 bg-danger d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="pending">Pending</span>`; break;
                        case "Rejected":        statusBadge = `<span class="badge p-2 bg-warning d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="rejected">Rejected</span>`; break;
                        case "In Progress":     statusBadge = `<span class="badge p-2 bg-info text-dark d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="inProgress">In Progress</span>`; break;
                        case "Awaiting Report":statusBadge = `<span class="badge p-2 bg-primary d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="awaitingReport">Awaiting Report</span>`; break;
                        case "Completed":       statusBadge = `<span class="badge p-2 bg-success d-inline-block w-75 w-sm-75 w-md-50 w-lg-25" data-lang-key="completed">Completed</span>`; break;
                    }
    
                    const modonApproved  = mission.approval_status?.modon_admin_approved;
                  
                    const regionApproved = mission.approval_status?.region_manager_approved;
                    const pilotApproved = mission.approval_status?.pilot_approved;
                    const gmanagerApproved	 = mission.approval_status?.general_manager_approved;
                   
                    const getStatusBadge = value => {
                        switch (value) {
                            case 1: return `<strong class="text-success" data-lang-key="Approved">Approved</strong>`;
                            case 2: return `<strong class="text-danger" data-lang-key="Rejected">Rejected</strong>`;
                            default: return `<strong class="text-warning" data-lang-key="Pending">Pending</strong>`;
                        }
                    };
    
                    const modonManagerStatus  = getStatusBadge(modonApproved);
                    const regionManagerStatus = getStatusBadge(regionApproved);
                    const pilotApprovedStatus = getStatusBadge(pilotApproved);
                    const gmanagerApprovedStatus = getStatusBadge(gmanagerApproved);
               
                    // ✅ Conditional edit/delete buttons (modon_admin only)
                    let editButton = '';
                    let deleteButton = '';
                    let viewReportButton = '';
                    if (userType === 'modon_admin' || userType === 'region_manager'|| userType === 'general_manager') {
                        const reportSubmitted = mission.report_submitted;
                        const pilotApproved = mission.approval_status?.pilot_approved;
                    
                      
                    
                     
                            if ((userType === 'modon_admin' || userType === 'region_manager'|| userType === 'general_manager') && mission.status === 'Pending' && modonApproved === 0 ) {
                                editButton = `<img src="./images/edit.png" alt="Edit" class="edit-mission img-fluid actions" data-id="${mission.id}">`;
                            }
                        
                            if ((userType === 'modon_admin' || userType === 'region_manager'|| userType === 'general_manager') && mission.status !== 'Rejected' && modonApproved === 0 ) {
                                deleteButton = `<img src="./images/delete.png" alt="Delete" class="delete-mission img-fluid actions" data-id="${mission.id}">`;
                            }
                        
                    }
                    


 
                    // }
                    if(mission.report_submitted === 1){
                        viewReportButton = `<img src="./images/view-report.png" alt="Delete" class="viewMissionReport img-fluid actions" data-id="${mission.id}">`;
                      
                    }

                    let approvalButtons = '';

                    if (mission.status === "Pending") {
                      
                        if (userType === 'general_manager' && gmanagerApproved === 0  && regionApproved === 1) {
                           console.log("m ",userType)
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
                           
                        } else if (userType === 'region_manager' &&  regionApproved === 0) {
                      
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
                        } else if (userType === 'modon_admin' && gmanagerApproved === 1 && regionApproved === 1 &&  modonApproved === 0 ) {
                        
                            console.log("m in",userType)
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
                    }
                    let approvalIndicator = approvalButtons ? `
                        <span class="position-absolute top-25 start-0 translate-middle p-1 bg-danger rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    ` : '';
                    // ✅ Final row HTML
                    const row = `
                        <div class="accordion-item" id="missionRow-${mission.id}" data-pilot-id="${mission.pilot_id}">

                            <h2 class="accordion-header  position-relative" id="heading-${mission.id}">
                                <button class="accordion-button collapsed d-flex px-3 py-2 " type="button">
                                   
                                   ${approvalIndicator}
                                    <div class="row w-100 justify-content-between label-text">
                                        <div class="col-3 ps-2  d-flex align-items-center justify-content-start" data-lang-key="${toLangKey(inspectionName)}" data-name="${inspectionName}" data-incident-name="${inspectionName}" data-inspectiontype-id="${inspectionId}">${inspectionName}</div>
                                        <div class="col-2 ps-4  d-flex align-items-center justify-content-start mission_date">${mission.mission_date}</div>
                                        <div class="col-3 text-center" data-lang-key ="${formatCityNames(locations)}" data-location-name="${locations}">${locations}</div>
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
                                             <span class="grayishytext" data-lang-key="${toLangKey(inspectionName)}">${inspectionName}</span>
                                        </div>
                                        <div class="col-lg-6 text-end">
                                             ${approvalButtons}
                                        </div>
                                        <div class="col-lg-4 ">
                                            <strong class="py-3" data-location-id="${firstLocation.id}" data-region-id="${regionId}" data-region-name="${regionName}" data-lang-key="locations"> Locations </strong><br>
                                            <span class="grayishytext"><span data-lang-key="${formatCityNames(locations)}">${locations}</span> ( <span data-lang-key="${regionName}">${regionName} </span> )</span>       
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
                                            <strong class="py-3"data-lang-key="missionCreatedBy">Mission Created By<br></strong> <span class="text-capitalize grayishytext" data-mission-created-by-name="${mission.created_by.name}">${mission.created_by.name}</span>(<span data-lang-key="${mission.created_by.user_type}">${mission.created_by.user_type}</span>) 
                                        </div>
                                        <div class="col-lg-12 border-bottom"> 
                                            <strong class="py-3" data-lang-key="note">Note</strong><br><span class="grayishtext" data-mission-note="${fullNote}"> ${fullNote}
                                        </div> 
                                        <div class="col-lg-12">
                                            <div class="row w-100 align-items-center">
                                                <strong data-lang-key="missionApproval">Mission Approval</strong><br>
                                                <div class="col-3 label-text"><p data-lang-key="test"><span data-lang-key="modonAdmin">Modon Admin:</span> ${modonManagerStatus}</p></div>
                                                <div class="col-3 label-text"><p><span data-lang-key="generalManager">General Manager:</span> ${gmanagerApprovedStatus}</p></div>
                                                <div class="col-3 label-text"><p><span data-lang-key="regionManager">Region Manager:</span> ${regionManagerStatus}</p></div>
                                                <div class="col-3 label-text"><p><span data-lang-key="pilot">Pilot:</span> ${pilotApprovedStatus}</p></div>
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
        $("#viewprogramInfo").text(inspectionName).attr("data-lang-key", formatCityNames(inspectionName));
        $("#viewregionInfo").text(regionName).attr("data-lang-key",regionName);
        $("#viewlocationInfo").text(locationName).attr("data-lang-key", toLangKey(inspectionName));
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
        let currentLang = localStorage.getItem("selectedLang") || "ar";
        updateLanguageTexts(currentLang);
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
    
    let errors = [];

// Define translations for error messages
const translations = {
    en: {
        mission_date_required: "Mission date is required.",
        inspection_type_required: "Please select an inspection type.",
        region_required: "Please select a Region.",
        pilot_required: "Please select a pilot.",
        latitude_required: "Please Enter a latitude.",
        longitude_required: "Please Enter a longitude.",
        location_required: "Please select at least one location.",
        missing_information: "Missing Information!"
    },
    ar: {
        mission_date_required: "تاريخ المهمة مطلوب.",
        inspection_type_required: "يرجى اختيار نوع التفتيش.",
        region_required: "يرجى اختيار منطقة.",
        pilot_required: "يرجى اختيار طيار.",
        latitude_required: "يرجى إدخال خط العرض.",
        longitude_required: "يرجى إدخال خط الطول.",
        location_required: "يرجى اختيار موقع واحد على الأقل.",
        missing_information: "معلومات مفقودة!"
    }
};

// Get the selected language from localStorage
const selectedLang = localStorage.getItem("selectedLang") || "en";

// Use the selected language for error messages
const lang = translations[selectedLang] || translations.en;

if (!formData.mission_date) errors.push(lang.mission_date_required);
if (!inspectionType) errors.push(lang.inspection_type_required);
if (!region_id) errors.push(lang.region_required);
if (!pilotId) errors.push(lang.pilot_required);
if (!latitude) errors.push(lang.latitude_required);
if (!longitude) errors.push(lang.longitude_required);
if (selectedLocations.length === 0) errors.push(lang.location_required);

if (errors.length > 0) {
    Swal.fire({
        icon: 'error',
        title: lang.missing_information,
        html: `<ul style="text-align:start;">${errors.map(err => `<li>${err}</li>`).join('')}</ul>`,
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
            const rolesToDisable = ['modon_admin', 'region_manager', 'general_manager','qss_admin'];
                const userRole =  $('#userTypeFront').attr('data-lang-key');
        
                if (rolesToDisable.includes(userRole)) {
                    $('#CreateMissionBtn').prop('disabled', true);
                }
            // Define translations for success messages
    const successTranslations = {
        en: {
            mission_saved: "Mission Saved!",
            mission_created: "Mission created successfully!",
            mission_updated: "Mission updated successfully!"
        },
        ar: {
            mission_saved: "تم حفظ المهمة!",
            mission_created: "تم إنشاء المهمة بنجاح!",
            mission_updated: "تم تحديث المهمة بنجاح!"
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for success messages
    const successLang = successTranslations[selectedLang] || successTranslations.en;


           // Determine the appropriate message based on the backend response
    const successMessage = response.message.includes("created")
        ? successLang.mission_created
        : successLang.mission_updated;

    Swal.fire({
        icon: 'success',
        title: successLang.mission_saved, // Use translated title
        text: successMessage, // Use the appropriate translated message
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
            console.log("Mission Created Data",response)
      

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
    // Define translations for SweetAlert titles
    const swalTitles = {
        en: {
            validation_error: "Validation Error!",
            server_error: "Error!",
            other_error: "Error!"
        },
        ar: {
            validation_error: "خطأ في التحقق!",
            server_error: "خطأ!",
            other_error: "خطأ!"
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert titles
    const swalLang = swalTitles[selectedLang] || swalTitles.en;

    if (xhr.status === 422) {
        // Handle validation errors
        const errors = xhr.responseJSON?.errors || {};
        const errorMessages = Object.keys(errors).map(key => {
            const errorKey = key.replace('.', '_'); // Convert "locations.*" to "locations_*"
            return translateBackendError(`${errorKey}_${errors[key][0].includes('required') ? 'required' : 'invalid'}`);
        });

        Swal.fire({
            icon: 'error',
            title: swalLang.validation_error, // Use translated title
            html: `<ul style="text-align:start;">${errorMessages.map(err => `<li>${err}</li>`).join('')}</ul>`,
            background: '#101625',
            color: '#ffffff'
        });
    } else if (xhr.status === 500) {
        // Handle server exceptions
        const errorMessage = translateBackendError('mission_creation_failed');

        Swal.fire({
            icon: 'error',
            title: swalLang.server_error, // Use translated title
            text: errorMessage,
            background: '#101625',
            color: '#ffffff'
        });
    } else {
        // Handle other errors
        Swal.fire({
            icon: 'error',
            title: swalLang.other_error, // Use translated title
            text: xhr.responseJSON?.message || translateBackendError('something_went_wrong'),
            background: '#101625',
            color: '#ffffff'
        });
    }
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
        // Define translations for SweetAlert
    const swalTranslations = {
        en: {
            confirm_title: "Are you sure?",
            confirm_text: "This mission will be permanently deleted.",
            input_label: "Reason for Deletion",
            input_placeholder: "Type reason here...",
            confirm_button: "Yes, delete it!",
            cancel_button: "Cancel",
            success_title: "Deleted!",
            success_text: "Mission has been deleted.",
            error_title: "Error!",
            error_text: "Something went wrong."
        },
        ar: {
            confirm_title: "هل أنت متأكد؟",
            confirm_text: "سيتم حذف هذه المهمة نهائيًا.",
            input_label: "سبب الحذف",
            input_placeholder: "اكتب السبب هنا...",
            confirm_button: "نعم، احذفها!",
            cancel_button: "إلغاء",
            success_title: "تم الحذف!",
            success_text: "تم حذف المهمة.",
            error_title: "خطأ!",
            error_text: "حدث خطأ ما."
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert translations
    const swalLang = swalTranslations[selectedLang] || swalTranslations.en;

        Swal.fire({
        title: swalLang.confirm_title,
        text: swalLang.confirm_text,
        icon: 'warning',
        input: 'textarea',
        inputLabel: swalLang.input_label,
        inputPlaceholder: swalLang.input_placeholder,
        inputAttributes: {
            'aria-label': swalLang.input_label
        },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: swalLang.confirm_button,
        cancelButtonText: swalLang.cancel_button
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
                    console.log(response);
                    sendMissionNotification({
                        mission: response.mission,
                        recipients: response.mission.allmails,
                        action: 'deleted'
                    });
                    Swal.fire({
                        icon: 'success',
                        title: swalLang.success_title,
                        text: swalLang.success_text,
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#101625',
                        color: '#ffffff'
                    });

                    $('#missionRow-' + missionId).remove(); // Remove mission row
                },
                error: function (xhr) {
                    let errorMessage;

                    // Handle specific backend error keys
                    if (xhr.status === 403) {
                        if (xhr.responseJSON?.error === "You are not authorized to delete this mission.") {
                            errorMessage = translateBackendError("unauthorized_delete");
                        } else if (xhr.responseJSON?.error === "❌ This mission has already been approved. Only the region manager or modon admin can delete it.") {
                            errorMessage = translateBackendError("mission_already_approved");
                        }
                    } else if (xhr.status === 422) {
                        if (xhr.responseJSON?.error === "Please provide a reason for deleting this mission.") {
                            errorMessage = translateBackendError("delete_reason_required");
                        }
                    } else {
                        errorMessage = translateBackendError("something_went_wrong");
                    }

                    // Display the translated error message in SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title:backendErrorTranslations[localStorage.getItem("selectedLang") || "en"].server_error, // Always use the translated "Error" from backendLang
                        text: errorMessage,
                        background: '#101625',
                        color: '#ffffff',
                        confirmButtonColor: '#d33'
                    });
}
            });
        }
    });
    
        // Swal.fire({
        //     title: 'Are you sure?',
        //     text: "This mission will be permanently deleted.",
        //     icon: 'warning',
        //     input: 'textarea',
        //     inputLabel: 'Reason for Deletion',
        //     inputPlaceholder: 'Type reason here...',
        //     inputAttributes: {
        //         'aria-label': 'Reason for deletion'
        //     },
        //     showCancelButton: true,
        //     confirmButtonColor: '#d33',
        //     cancelButtonColor: '#6c757d',
        //     confirmButtonText: 'Yes, delete it!',
        //     cancelButtonText: 'Cancel'
        // }).then((result) => {
        //     if (result.isConfirmed) {
        //         const deleteReason = result.value?.trim();
    
        //         $.ajax({
        //             url: `/missions/${missionId}`,
        //             type: "POST",
        //             data: {
        //                 delete_reason: deleteReason
        //             },
        //             success: function (response) {
        //                 console.log(response)
        //                 sendMissionNotification({
        //                     mission: response.mission,
        //                     recipients: response.mission.allmails,
        //                     action: 'deleted'
        //                 });
        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: 'Deleted!',
        //                     text: response.message || 'Mission has been deleted.',
        //                     timer: 2000,
        //                     showConfirmButton: false,
        //                     background: '#101625',
        //                     color: '#ffffff'
        //                 });
    
        //                 $('#missionRow-' + missionId).remove(); // Remove mission row
        //                 // getMissionStats();
        //             },
        //             error: function (xhr) {
        //                 const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
    
        //                 Swal.fire({
        //                     icon: 'error',
        //                     title: 'Error!',
        //                     text: errorMessage,
        //                     background: '#101625',
        //                     color: '#ffffff',
        //                     confirmButtonColor: '#d33'
        //                 });
        //             }
        //         });
        //     }
        // });
    });
    

    


        // Handle Edit Mission Button Click
        $(document).on("click", ".edit-mission", function () {
            $(".cancel-btn").removeClass("d-none");
            $('#CreateMissionBtn').prop('disabled', false);
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

                    $('#location_id').prop('disabled', true);
                }, 150);

            }
        
            // ✅ Location checkboxes (if present)
            $(".location-checkbox").each(function () {
                const labelText = $(this).siblings("label").text().trim().toLowerCase();
                $(this).prop("checked", locationNames.includes(labelText));
            });
            let currentLang = localStorage.getItem("selectedLang") || "ar";
            // ✅ Set form into “edit” mode
            $("#addMissionForm").attr("data-mission-id", missionId);
            if (currentLang === "ar") {
                //alert("ar");
                $(".form-title-mission").text("تحديث المهمة");
                $(".mission-btn span").text("تحديث المهمة");
            } else {
                $(".form-title").text("Update Mission");
                $(".mission-btn span").text("Update Mission");
                $(".mission-btn svg").attr({ "width": "30", "height": "30" });
            }
        
            // Get language
           
            // $(".form-title").text("Edit Mission");
            // $(".mission-btn span").text("Update Mission");
            // $(".mission-btn svg").attr({ "width": "30", "height": "30" });
        });
        
      
     

        
    
      
        function resetValues(){
            $("#addMissionForm")[0].reset();
            $('#location_id').prop('disabled', false);
            
            // ✅ Uncheck All Checkboxes
            $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
        
            // ✅ Restore Title & Button Text
            let currentLang = localStorage.getItem("selectedLang") || "ar";
            if (currentLang === "ar") {
                $(".form-title-mission").text("إنشاء مهمة");
                $(".mission-btn span").text("إنشاء مهمة");
            } else {
                $(".form-title").text("Create Mission");
                $(".mission-btn span").text("Create Mission");
                $(".mission-btn svg").attr({ "width": "30", "height": "30" });
            }
            
            // $(".form-title").text("Create New Mission");
            // $(".mission-btn span").text("Create Mission");
        
            // ✅ Remove Cancel Button
          
            //$(".mission-btn svg").attr({ "width": "16", "height": "16" });
            // ✅ Clear Mission ID
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
        }
        
        $(document).on("click", ".cancel-btn", function () {
            // ✅ Reset Form Fields
            resetValues();
            $('#CreateMissionBtn').prop('disabled', true);
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
                sendMissionNotification({
                    mission: response.mission,
                    recipients: response.allmails,
                    action: 'Updated'
                });
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
        // Define translations for SweetAlert
    const swalTranslations = {
        en: {
            sending_title: `Mission ${action === 'Created' ? 'Created' : action === 'Updated' ? 'Updated' : 'Deleted'}...`,
            sending_message: "Please wait while emails are being sent...",
            success_title: "Email Sent!",
            success_message: `Mission ${action === 'Created' ? 'created' : action === 'Updated' ? 'updated' : 'deleted'} notification sent successfully.`,
            error_title: "Email Error!",
            error_message: "An error occurred while sending the email."
        },
        ar: {
            sending_title: `المهمة ${action === 'Created' ? 'تم إنشاؤها' : action === 'Updated' ? 'تم تحديثها' : 'تم حذفها'}...`,
            sending_message: "يرجى الانتظار أثناء إرسال رسائل البريد الإلكتروني...",
            success_title: "تم إرسال البريد الإلكتروني!",
            success_message: `تم إرسال إشعار المهمة ${action === 'Created' ? 'تم إنشاؤها' : action === 'Updated' ? 'تم تحديثها' : 'تم حذفها'} بنجاح.`,
            error_title: "خطأ في البريد الإلكتروني!",
            error_message: "حدث خطأ أثناء إرسال البريد الإلكتروني."
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert translations
    const swalLang = swalTranslations[selectedLang] || swalTranslations.en;
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
                        <li><span style="font-weight:600;">Latitude:</span> <span style="color:#333;">${mission.locations?.[0]?.latitude || 'N/A'}</span></li>
                        <li><span style="font-weight:600;">Longitude:</span> <span style="color:#333;">${mission.locations?.[0]?.longitude || 'N/A'}</span></li>
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
                // Show loading modal
    Swal.fire({
        title: swalLang.sending_title,
        html: swalLang.sending_message,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // Dummy recipients for testing
    const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];

    // Send email request
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
            title: swalLang.success_title,
            text: swalLang.success_message,
            timer: 2000,
            showConfirmButton: false
        });
    })
    .catch(error => {
        console.error('Email send error:', error);
        Swal.fire({
            icon: 'error',
            title: swalLang.error_title,
            text: swalLang.error_message
        });
    });
            
                // // ✅ Show loading modal
                // Swal.fire({
                //     title: `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}...`,
                //     html: 'Please wait while emails are being sent...',
                //     allowOutsideClick: false,
                //     didOpen: () => Swal.showLoading()
                // });
                // const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
                // // ✅ Send email request
                // fetch('/send-email', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //     },
                //     body: JSON.stringify({ recipients: dummyRecipients, subject, content })
                // })
                // .then(res => res.json())
                // .then(data => {
                //     Swal.fire({
                //         icon: 'success',
                //         title: 'Email Sent!',
                //         text: data.message || `Mission ${action} notification sent successfully.`,
                //         timer: 2000,
                //         showConfirmButton: false
                //     });
                // })
                // .catch(error => {
                //     console.error('Email send error:', error);
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Email Error!',
                //         text: 'An error occurred while sending the email.'
                //     });
                // });
            }
function formatType(type) {
                if (!type) return 'N/A';
                return type
                  .split('_')
                  .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                  .join(' ');
              }

    function sendApprovalNotification({ mission, recipients, decision,missioninfo }) {

        const swalTranslations = {
        en: {
            sending_title: `Mission ${decision === 'approve' ? 'Approval' : 'Rejection'}...`,
            sending_message: "Please wait while emails are being sent...",
            success_title: "Email Sent!",
            success_message: `Mission ${decision === 'approve' ? 'approved' : 'rejected'} notification sent successfully.`,
            error_title: "Email Error!",
            error_message: "An error occurred while sending the email."
        },
        ar: {
            sending_title: `المهمة ${decision === 'approve' ? 'تمت الموافقة عليها' : 'تم رفضها'}...`,
            sending_message: "يرجى الانتظار أثناء إرسال رسائل البريد الإلكتروني...",
            success_title: "تم إرسال البريد الإلكتروني!",
            success_message: `تم إرسال إشعار المهمة ${decision === 'approve' ? 'تمت الموافقة عليها' : 'تم رفضها'} بنجاح.`,
            error_title: "خطأ في البريد الإلكتروني!",
            error_message: "حدث خطأ أثناء إرسال البريد الإلكتروني."
        }
    };

    // Get the selected language from localStorage
    const selectedLang = localStorage.getItem("selectedLang") || "en";

    // Use the selected language for SweetAlert translations
    const swalLang = swalTranslations[selectedLang] || swalTranslations.en;

            // Map user types to formatted strings
        const userTypeMap = {
            qss_admin: "QSS Admin",
            modon_admin: "Modon Admin",
            region_manager: "Region Manager",
            general_manager: "General Manager",
            pilot: "Pilot",
            city_manager: "City Manager"
        };
        console.log("Mission data for sending email", missioninfo);
        console.log("real recipients",recipients);
        // Get the formatted user type
        const formattedUserType = userTypeMap[mission.user_type] || "Unknown User";
            // Determine the action and email content based on the decision
            const action = decision == "approve" ? 'approved' : 'rejected';
            const subject = `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}`;
            //console.log("🚀 ~ file: missions.js:1 ~ sendApprovalNotification ~ missioninfo:", missioninfo)
        const content = `
        <p>Hello,</p>

       <p>A mission has been <strong style="color:${action === 'approved' ? 'green' : 'red'}">${action}</strong> 
            ${mission.user_name ? `<strong>${mission.user_name} (${formattedUserType})</strong>` : ''} 
            in the dashboard
        Please log in to your account to view the latest details.</p>

        <hr>

        <h3 style="margin-bottom: 5px;">📋 <u>Mission Details:</u></h3>
        <ul style="line-height: 1.6;">
            <li><strong>Program:</strong> ${missioninfo.program || 'N/A'}</li>
            <li><strong>Region:</strong> ${missioninfo.location.region || 'N/A'}</li>
            <li><strong>City:</strong> ${missioninfo.location.city || 'N/A'}</li>
             <li><strong>Mission Date:</strong> ${missioninfo.missionDate || 'N/A'}</li>
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
        // Show loading modal
    Swal.fire({
        title: swalLang.sending_title,
        html: swalLang.sending_message,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // Dummy recipients for testing
    const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];

    // Send email request
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
                title: swalLang.success_title,
                text: swalLang.success_message,
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Email send error:', error);
            Swal.fire({
                icon: 'error',
                title: swalLang.error_title,
                text: swalLang.error_message
            });
        });
            // // ✅ Show loading modal
            // Swal.fire({
            //     title: `Mission ${action.charAt(0).toUpperCase() + action.slice(1)}...`,
            //     html: 'Please wait while emails are being sent...',
            //     allowOutsideClick: false,
            //     didOpen: () => Swal.showLoading()
            // });
            // const dummyRecipients = ["nabeelabbasix@gmail.com", "nabeelabbasi050@gmail.com"];
            // // ✅ Send email request
            // fetch('/send-email', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     body: JSON.stringify({ recipients: dummyRecipients, subject, content })
            // })
            // .then(res => res.json())
            // .then(data => {
            //     Swal.fire({
            //         icon: 'success',
            //         title: 'Email Sent!',
            //         text: data.message || `Mission ${action} notification sent successfully.`,
            //         timer: 2000,
            //         showConfirmButton: false
            //     });
            // })
            // .catch(error => {
            //     console.error('Email send error:', error);
            //     Swal.fire({
            //         icon: 'error',
            //         title: 'Email Error!',
            //         text: 'An error occurred while sending the email.'
            //     });
            // });
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
    //     const missiondate    = $("#viewmissionDateInfo").text().trim();
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
    
    $(document).on('click', '.downloadReportbtn', function(e) {
        e.preventDefault();
    
        // Fetch the information
        const missionOwner   = $("#viewOwnerInfo").text().trim();
        const pilot          = $("#viewpilotInfo").text().trim();
        const region         = $("#viewregionInfo").text().trim();
        const program        = $("#viewprogramInfo").text().trim();
        const location       = $("#viewlocationInfo").text().trim();
        const geoCoordinates = $("#viewgeoInfo").text().trim();
        const missiondate    = $("#viewmissionDateInfo").text().trim();
        const description    = $("#description").text().trim();
    
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
            images: images,
            missiondate:missiondate,
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
                Swal.close(); // ✅ Close the loader on success
    
                const blob = new Blob([response], { type: 'application/pdf' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Mission_Report.pdf';
                link.click();
            },
            error: function(xhr) {
                Swal.close(); // Close loading
                Swal.fire('Failed!', 'PDF download failed. Please try again.', 'error');
                console.error('PDF download failed');
            }
        });
    });
    
    // $(".search-icon-mission").click(function () {
    //     $(".search-input-mission").toggleClass("active").show().focus();
    // });

});
