// 1. Generate session dropdown options
function getSessionHTML(rv) {
    let x = `<option value="-1">SELECT ONE</option>`;
    for (let i = 0; i < rv.length; i++) {
        let cs = rv[i];
        x += `<option value="${cs['id']}">${cs['year']} ${cs['term']}</option>`;
    }
    return x;
}

// 2. Load sessions from server
function loadSessions() {
    $.ajax({
        url: "ajaxhandler/attendanceAJAX.php",
        type: "POST",
        dataType: "json",
        data: { action: "getSession" },
        success: function (rv) {
            let html = getSessionHTML(rv);
            $("#ddlclass").html(html);
        },
        error: function () {
            alert("Failed to load sessions.");
        }
    });
}

// 3. Generate faculty course cards
function getCourseCardHTML(classlist) {
    let x = ``;
    for (let i = 0; i < classlist.length; i++) {
        let cc = classlist[i];
        x += `<div class="classcard" data-classobject='${JSON.stringify(cc)}'>${cc['code']}</div>`;
    }
    return x;
}

// 4. Fetch courses by faculty and session
function fetchFacultyCourses(facid, sessionid) {
    $.ajax({
        url: "ajaxhandler/attendanceAJAX.php",
        type: "POST",
        dataType: "json",
        data: { facid: facid, sessionid: sessionid, action: "getFacultyCourses" },
        success: function (rv) {
            let html = getCourseCardHTML(rv);
            $("#classlistarea").html(html);
        },
        error: function () {
            alert("Failed to load courses.");
        }
    });
}

// 5. Generate course header (no date)
function getClassdetailsAreaHTML(classobject) {
    return `
        <div class="classdetails">
            <div class="code-area">${classobject['code']}</div>
            <div class="title-area">${classobject['title']}</div>
        </div>`;
}

// 6. Generate student mark input list
function getStudentListHTML(studentList) {
    let x = `<div class="studenttlist"><label>STUDENT LIST</label></div>`;
    for (let i = 0; i < studentList.length; i++) {
        let cs = studentList[i];
        let mark = cs['marks'] ?? "";
        x += `
            <div class="studentdetails">
                <div class="slno-area">${i + 1}</div>
                <div class="rollno-area">${cs['roll_no']}</div>
                <div class="name-area">${cs['name']}</div>
                <div class="markinput-area">
                    <input type="number" min="0" max="100" class="markinput" data-studentid="${cs['id']}" value="${mark}">
                    <button class="btnSaveIndividualMark" data-studentid="${cs['id']}">Save</button>
                </div>
            </div>`;
    }
    x += `
        <div class="reportsection">
            <button id="btnDownloadCSV">Download CSV</button>
        </div>`;
    return x;
}

// 7. Fetch student list with existing marks (if any)
function fetchStudentList(sessionid, classid, facid) {
    $.ajax({
        url: "ajaxhandler/marksAJAX.php",
        type: "POST",
        dataType: "json",
        data: {
            action: "getStudentList",
            sessionid: sessionid,
            courseid: classid,
            facid: facid
        },
        success: function (rv) {
            let html = getStudentListHTML(rv);
            $("#studentlistarea").html(html);
        },
        error: function () {
            alert("Failed to fetch student list.");
        }
    });
}

// 8. Page ready: bind all events
$(function () {
    loadSessions();

    $(document).on("click", "#btnLogout", function () {
        $.ajax({
            url: "ajaxhandler/logoutAjax.php",
            type: "POST",
            dataType: "json",
            data: { id: 1 },
            success: function () {
                document.location.replace("login.php");
            },
            error: function () {
                alert("Something went wrong!");
            }
        });
    });

    $(document).on("click", "#btnAttendance", function () {
        window.location.href = "attendance.php";
    });
    $(document).on("click", "#btnAcademic", function () {
    window.location.href = "admin.php";
    });

    // When a session is selected
    $(document).on("change", "#ddlclass", function () {
        let sessionid = $(this).val();
        $("#classlistarea").empty();
        $("#classdetailsarea").empty();
        $("#studentlistarea").empty();

        if (sessionid != -1) {
            let facid = $("#hiddenFacId").val();
            fetchFacultyCourses(facid, sessionid);
        }
    });

    // When a course is clicked
    $(document).on("click", ".classcard", function () {
        let classobject = $(this).data("classobject");
        $("#hiddenSelectedCourseID").val(classobject['id']);

        let html = getClassdetailsAreaHTML(classobject);
        $("#classdetailsarea").html(html);

        let sessionid = $("#ddlclass").val();
        let classid = classobject['id'];
        let facid = $("#hiddenFacId").val();

        fetchStudentList(sessionid, classid, facid); 
    });


    // When the CSV download button is clicked
    $(document).on("click", "#btnDownloadCSV", function () {
    const sessionId = $("#ddlclass").val();
    const courseId = $("#hiddenSelectedCourseID").val();

    if (!sessionId || !courseId || sessionId == "-1") {
        alert("Please select session and course.");
        return;
    }

    // Construct CSV download URL
    const url = `ajaxhandler/marksAJAX.php?action=downloadCSV&sessionid=${sessionId}&courseid=${courseId}`;
    window.open(url, "_blank"); // triggers download
    });


    // Save individual mark
    $(document).on("click", ".btnSaveIndividualMark", function () {
        let studentId = $(this).data("studentid");
        let mark = $(`.markinput[data-studentid='${studentId}']`).val();

        if (mark === "" || isNaN(mark) || mark < 0 || mark > 100) {
            alert("Invalid mark");
            return;
        }

        let sessionId = $("#ddlclass").val();
        let courseId = $("#hiddenSelectedCourseID").val();
        let facid = $("#hiddenFacId").val();
        let grade = ""; // optional

        $.ajax({
            url: "ajaxhandler/marksAJAX.php",
            type: "POST",
            dataType: "json",
            data: {
                action: "saveMarks",
                sessionid: sessionId,
                courseid: courseId,
                studentid: studentId,
                marks: mark,
                grade: grade
            },
            success: function () {
                alert("Saved!");
            },
            error: function () {
                alert("Save failed.");
            }
        });
    });
});
