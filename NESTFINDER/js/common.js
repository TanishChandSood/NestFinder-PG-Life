window.addEventListener("load", function () {
  var signup_form = document.getElementById("signup-form");
  if (signup_form) {
    signup_form.addEventListener("submit", function (event) {
      var XHR = new XMLHttpRequest();
      var form_data = new FormData(signup_form);

      XHR.addEventListener("load", signup_success);
      XHR.addEventListener("error", on_error);
      XHR.open("POST", "api/signup_submit.php");
      XHR.send(form_data);

      var loading_el = document.getElementById("loading");
      if (loading_el) loading_el.style.display = "block";
      event.preventDefault();
    });
  }

  var login_form = document.getElementById("login-form");
  if (login_form) {
    login_form.addEventListener("submit", function (event) {
      var XHR = new XMLHttpRequest();
      var form_data = new FormData(login_form);

      XHR.addEventListener("load", login_success);
      XHR.addEventListener("error", on_error);
      XHR.open("POST", "api/login_submit.php");
      XHR.send(form_data);

      var loading_el = document.getElementById("loading");
      if (loading_el) loading_el.style.display = "block";
      event.preventDefault();
    });
  }

  document.addEventListener("click", function (event) {
    var target = event.target.closest(".interested-container");

    if (target) {
      if (target.classList.contains("is-processing")) {
        return;
      }

      var icon_el = target.querySelector(".is-interested-image");
      if (!icon_el) return;

      var property_id = icon_el.getAttribute("property_id");

      if (!property_id) {
        var searchParams = new URLSearchParams(window.location.search);
        property_id = searchParams.get("property_id");
      }

      if (!property_id) {
        alert("Property ID not found!");
        return;
      }

      target.classList.add("is-processing");

      var XHR = new XMLHttpRequest();
      XHR.addEventListener("load", function (e) {
        target.classList.remove("is-processing");
        toggle_interested_success(e, target, icon_el);
      });
      XHR.addEventListener("error", function (e) {
        target.classList.remove("is-processing");
        on_error(e);
      });

      XHR.open(
        "GET",
        "api/toggle_interested_status.php?property_id=" +
          encodeURIComponent(property_id),
      );
      XHR.send();
    }
  });
});

var toggle_interested_success = function (event, targetContainer, clickedIcon) {
  try {
    var response = JSON.parse(event.target.responseText);

    if (response.success) {
      var cardContainer =
        targetContainer.closest(".property-card") ||
        targetContainer.closest(".content-container") ||
        document;
      var interested_user_count = cardContainer.querySelector(
        ".interested-user-count",
      );

      if (response.is_interested) {
        if (clickedIcon) {
          clickedIcon.classList.add("fas");
          clickedIcon.classList.remove("far");
        }
        if (interested_user_count) {
          var current_count = parseInt(
            interested_user_count.innerHTML.trim(),
            10,
          );
          interested_user_count.innerHTML = isNaN(current_count)
            ? 1
            : current_count + 1;
        }
      } else {
        if (clickedIcon) {
          clickedIcon.classList.add("far");
          clickedIcon.classList.remove("fas");
        }
        if (interested_user_count) {
          var current_count = parseInt(
            interested_user_count.innerHTML.trim(),
            10,
          );
          interested_user_count.innerHTML =
            isNaN(current_count) || current_count <= 0 ? 0 : current_count - 1;
        }
      }
    } else if (!response.is_logged_in) {
      if (window.$ && window.$.fn && window.$.fn.modal) {
        $("#login-modal").modal("show");
      } else {
        alert("Please login first!");
      }
    } else {
      alert(response.message || "An error occurred.");
    }
  } catch (error) {
    alert("System Error:\n\nCould not process backend response safely.");
  }
};

var signup_success = function (event) {
  var loading_el = document.getElementById("loading");
  if (loading_el) loading_el.style.display = "none";

  try {
    var response = JSON.parse(event.target.responseText);
    if (response.success) {
      alert(response.message);
      window.location.href = "index.php";
    } else {
      alert(response.message);
    }
  } catch (e) {
    alert("Signup response was invalid.");
  }
};

function on_error(event) {
  var loading_el = document.getElementById("loading");
  if (loading_el) loading_el.style.display = "none";
  alert("Oops! Something went wrong.");
}

function login_success(event) {
  var loading_el = document.getElementById("loading");
  if (loading_el) loading_el.style.display = "none";

  try {
    var response = JSON.parse(event.target.responseText);
    if (response.success) {
      if (response.role === "owner") {
        window.location.replace("owner_dashboard.php");
      } else {
        location.reload();
      }
    } else {
      alert(response.message);
    }
  } catch (e) {
    alert("Login response was invalid.");
  }
}

var login_form = document.getElementById("login-form");
if (login_form) {
  login_form.addEventListener("submit", function (event) {
    var XHR = new XMLHttpRequest();
    var form_data = new FormData(login_form);

    XHR.addEventListener("load", login_success);
    XHR.addEventListener("error", on_error);
    XHR.open("POST", "api/login_submit.php");
    XHR.send(form_data);

    var loading_el = document.getElementById("loading");
    if (loading_el) loading_el.style.display = "block";
    event.preventDefault();
  });
}
