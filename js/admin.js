$(document).ready(function () {
    // Logout button
    $(document).on("click", "#btnLogout", function () {
        $.ajax({
            url: "ajaxhandler/logoutAjax.php",
            type: "POST",
            dataType: "json",
            success: function () {
                window.location.replace("login.php");
            },
            error: function () {
                alert("Something went wrong during logout!");
            }
        });
    });

    // Attendance button
    $(document).on("click", "#btnAttendance", function () {
        window.location.href = "attendance.php";
    });

    // Populate year and term dropdowns for Add Class form
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y <= currentYear + 5; y++) {
        $("#classYear").append(`<option value="${y}">${y}</option>`);
    }
    ["Fall", "Summer", "Spring"].forEach(term => {
        $("#classTerm").append(`<option value="${term}">${term}</option>`);
    });

    // Load sessions, faculties, and classes for Assign Classes form
    function loadSessions() {
        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "getSessions" },
            dataType: "json",
            success: function (data) {
                $("#assignSession").empty();
                data.forEach(session => {
                    $("#assignSession").append(`<option value="${session.id}">${session.year} - ${session.term}</option>`);
                });
                loadClasses();
            }
        });
    }

    function loadClasses() {
        let sessionId = $("#assignSession").val();
        if (!sessionId) return;
        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "getClassesBySession", session_id: sessionId },
            dataType: "json",
            success: function (data) {
                $("#assignClass").empty();
                data.forEach(course => {
                    $("#assignClass").append(`<option value="${course.id}">${course.code} - ${course.title}</option>`);
                });
            }
        });
    }

    function loadFaculties() {
        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "getFaculties" },
            dataType: "json",
            success: function (data) {
                $("#assignFaculty").empty();
                data.forEach(faculty => {
                    $("#assignFaculty").append(`<option value="${faculty.id}">${faculty.name} (${faculty.user_name})</option>`);
                });
            }
        });
    }

    // On session change, reload classes
    $("#assignSession").change(function () {
        loadClasses();
    });

    loadSessions();
    loadFaculties();

    // Form 1: Add New Session
    $("#formAddSession").submit(function (e) {
        e.preventDefault();
        const year = $("#sessionYear").val();
        const term = $("#sessionTerm").val();

        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "addSession", year: year, term: term },
            dataType: "json",
            success: function (response) {
                $("#sessionMessage").text(response.message);
                if (response.status === "success") {
                    loadSessions();
                }
            }
        });
    });

    // Form 2: Add New Class (Course)
    $("#formAddClass").submit(function (e) {
        e.preventDefault();
        const year = $("#classYear").val();
        const term = $("#classTerm").val();
        const code = $("#courseCode").val().trim();
        const title = $("#courseTitle").val().trim();
        const credit = $("#courseCredit").val();

        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "addClass", year, term, code, title, credit },
            dataType: "json",
            success: function (response) {
                $("#classMessage").text(response.message);
                if (response.status === "success") {
                    loadClasses();
                }
            }
        });
    });

    // Form 3: Add New Faculty
    $("#formAddFaculty").submit(function (e) {
        e.preventDefault();
        const user_name = $("#facultyUsername").val().trim();
        const name = $("#facultyName").val().trim();
        const password = $("#facultyPassword").val();
        const is_admin = $("#facultyIsAdmin").is(":checked") ? 1 : 0;

        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "addFaculty", user_name, name, password, is_admin },
            dataType: "json",
            success: function (response) {
                $("#facultyMessage").text(response.message);
                if (response.status === "success") {
                    loadFaculties();
                }
            }
        });
    });

    // Form 4: Assign Classes to Faculty
    $("#formAssignClass").submit(function (e) {
        e.preventDefault();
        const session_id = $("#assignSession").val();
        const course_id = $("#assignClass").val();
        const faculty_id = $("#assignFaculty").val();

        $.ajax({
            url: "ajaxhandler/adminAJAX.php",
            type: "POST",
            data: { action: "assignClass", session_id, course_id, faculty_id },
            dataType: "json",
            success: function (response) {
                $("#assignMessage").text(response.message);
            }
        });
    });
});
