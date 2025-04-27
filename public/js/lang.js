$(document).ready(function () {
    const languageFile = {
        pilots: {
            en: "Pilots",
            ar: "الطيارين",
        },
        drones:{
            en: "Drones",
            ar: "الطائرات بدون طيار",
        },
        missions: {
            en: "Missions",
            ar: "المهام",
        },
        reigons: {
            en: "Regions",
            ar: "المناطق",
        },
        locations: {
            en: "Locations",
            ar: "المواقع",
        },
        missionAnaltyics: {
            en: "Missions Analytics",
            ar: "تحليلات المهام",
        },
        noDataFound: {
            en: "No data found",
            ar: "لا توجد بيانات",
        },
        latestMissions: {
            en: "Latest Missions",
            ar: "آخر المهام",
        },
        "loading...": {
            en: "Loading...",
            ar: "...تحميل",
        },
        latestIncidents: {
            en: "Latest Incidents",
            ar: "آخر الأحداث",
        },
        pilotTracking: {
            en: "Pilot Tracking",
            ar: "تتبع الطيار",
        },
        startDate: {
            en: "Start Date",
            ar: "تاريخ البدء",
        },
        endDate: {
            en: "End Date",
            ar: "تاريخ الانتهاء",
        },
        pending: {
            en: "Pending",
            ar: "قيد الانتظار",
        },
        finished: {
            en: "Finished",
            ar: "منتهي",
        },
        totalMissions: {
            en: "Total Missions",
            ar: "إجمالي المهام",
        },
        loading: {
            en: "Loading",
            ar: "تحميل",
        },
        centeralRegion: {
            en: "Central Region",
            ar: "المنطقة الوسطى",
        },
        "missions:": {
            en: "Missions :",
            ar: ": المهام",
        },
        easternRegion: {
            en: "Eastern Region",
            ar: "المنطقة الشرقية",
        },
        WesternRegion: {
            en: "Western Region",
            ar: "المنطقة الغربية",
        },
        restView: {
            en: "Reset View",
            ar: "إعادة عرض",
        },
        "totalMissions:": {
            en: "Total Missions :",
            ar: ": إجمالي المهام",
        },
        dashboard: {
            en: "Dashboard",
            ar: "لوحة التحكم",
        },
        locations: { // Added this key
            en: "Locations",
            ar: "المواقع",
        },
        users: { // Added this key
            en: "Users",
            ar: "المستخدمين",
        },
        Dashboard: { // Added this key
            en: "Dashboard",
            ar: "لوحة التحكم",
        },
        Missions: { // Added this key
            en: "Missions",
            ar: "البعثات",
        },
        Locations: { // Added this key
            en: "Locations",
            ar: "المواقع",
        },
        Users: { // Added this key
            en: "Users",
            ar: "المستخدمين",
        },
        Drones: { // Added this key
            en: "Drones",
            ar: "طيار",
        },
    };
    // Get language from localStorage, fallback to English
    let currentLang = localStorage.getItem("selectedLang") || "en";

    // Function to update language text
    function updateLanguageTexts(lang) {
        $("[data-lang-key]").each(function () {
            const key = $(this).data("lang-key");
            const translation = languageFile[key]?.[lang];
            if (translation) {
                $(this).text(translation);
            }
        });
    }

    // Function to update text direction
    function updateTextDirection(lang) {
        $("body").attr("dir", lang === "ar" ? "rtl" : "ltr");
    }

    // Initialize language texts and direction
    updateLanguageTexts(currentLang);
    // updateTextDirection(currentLang);

    // Update language on dropdown click
    $(".lang-option").on("click", function (e) {
        e.preventDefault();

        // Get the clicked flag image source
        const selectedFlag = $(this).find('img').attr('src');

        // Set the selected flag image
        $(".selected-flag").attr("src", selectedFlag);

        // Optionally, store in localStorage
        const selectedLang = $(this).data("lang");
        localStorage.setItem("selectedLang", selectedLang);


        updateLanguageTexts(selectedLang);
   
        
        $('#langDropdown').removeClass('active');
        
    });

    

    // Flag map to set correct flag on page load
    const flagMap = {
        en: "./images/language-selection/english.png",
        ar: "./images/language-selection/arabic.png",
    };

    // Load language and flag on page load
    $("#selected-flag").attr("src", flagMap[currentLang]);


    
});