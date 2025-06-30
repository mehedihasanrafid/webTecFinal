function tryLogin() {
    const username = $("#txtUsername").val().trim();
    const password = $("#txtPassword").val().trim();

    if (username !== "" && password !== "") {
        $.ajax({
            url: "ajaxhandler/loginAjax.php",
            type: "POST",
            dataType: "json",
            data: {
                user_name: username,
                password: password,
                action: "verifyUser"
            },
            beforeSend: function () {
                $("#diverror").removeClass("applyerrordiv");
                $("#lockscreen").addClass("applylockscreen");
            },
            success: function (response) {
                $("#lockscreen").removeClass("applylockscreen");

                if (response.status === "ALL OK") {
                    // Redirect based on admin status
                    if (response.is_admin == 1) {
                        window.location.href = "admin.php";
                    } else {
                        window.location.href = "attendance.php";
                    }
                } else {
                    $("#diverror").addClass("applyerrordiv");
                    $("#errormessage").text(response.status);
                }
            },
            error: function () {
                alert("Oops, something went wrong. Please try again.");
            }
        });
    }
}

$(document).ready(function () {
    // Enable or disable login button on keyup
    $(document).on("keyup", "input", function () {
        $("#diverror").removeClass("applyerrordiv");

        const username = $("#txtUsername").val().trim();
        const password = $("#txtPassword").val().trim();

        if (username !== "" && password !== "") {
            $("#btnLogin").removeClass("inactivecolor").addClass("activecolor");
        } else {
            $("#btnLogin").removeClass("activecolor").addClass("inactivecolor");
        }
    });

    // Handle login button click
    $(document).on("click", "#btnLogin", function () {
        tryLogin();
    });
});
