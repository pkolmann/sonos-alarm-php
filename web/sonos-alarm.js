function showSection(section) {
    const sections = document.getElementsByClassName("content-section");
    for (let i = 0; i < sections.length; i++) {
        sections[i].classList.remove("active-section");
    }
    document.getElementById(section).classList.add("active-section");
}

function toggleAlarm(alarmId) {
    console.log("Toggle alarm: " + alarmId);

    // activate the loader
    document.getElementById("loader")['style'].display = "block";

    fetch("api.php?cmd=toggleAlarm&alarmId=" + alarmId)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            document.getElementById("loader")['style'].display = "none";
            return response.text();
        })
        .then(data => {
            console.log(data);

            // Update the UI
            const alarmEnabled = document.getElementById("alarm-" + alarmId + "-enabled");
            const alarmIcon = document.getElementById("alarm-" + alarmId + "-icon");
            if (data === "active") {
                console.log("updating to active");
                alarmEnabled.innerHTML = "<b>Enabled:</b> Active now (timestamp: " + new Date().toLocaleTimeString() + ")";
                alarmIcon.innerHTML = "⏰";

                // reset the loader
                document.getElementById("loader")['style'].display = "none";
            } else {
                console.log("updating to dormant");
                alarmEnabled.innerHTML = "<b>Enabled:</b> Dormant now (timestamp: " + new Date().toLocaleTimeString() + ")";
                alarmIcon.innerHTML = "😴";

                // reset the loader
                document.getElementById("loader")['style'].display = "none";
            }
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);

            // reset the loader
            document.getElementById("loader")['style'].display = "none";
        });
    return true;
}

function editAlarm(alarmId) {
    console.log("Edit alarm: ", alarmId);
    return true;
}

function deleteAlarm(alarmId) {
    console.log("Deleting alarm: ", alarmId)

    // activate the loader
    document.getElementById("loader")['style'].display = "block";

    fetch("api.php?cmd=deleteAlarm&alarmId=" + alarmId)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            document.getElementById("loader")['style'].display = "none";
            return response.text();
        })
        .then(data => {
            console.log(data);

            const json = JSON.parse(data);
            console.log("delete json: ", json);

            document.getElementById('alarm-' + alarmId + '-div').remove();

            // reset the loader
            document.getElementById("loader")['style'].display = "none";
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);

            // reset the loader
            document.getElementById("loader")['style'].display = "none";
        });
    return true;
}

function addAlarm() {
    const room = document.getElementById("room").valueOf().value;
    const time = document.getElementById("time").valueOf().value;
    const frequency = document.getElementById("frequency").valueOf().value;

    console.log("Add alarm: " + room + " " + time + " " + frequency);

    // activate the loader
    document.getElementById("loader")['style'].display = "block";

    fetch("api.php?cmd=addAlarm&room=" + room + "&time=" + time + "&frequency=" + frequency)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            showSection('home');
            return response.text();
        })
        .then(data => {
            console.log(data);

            // reset the loader
            document.getElementById("loader")['style'].display = "none";
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);

            // reset the loader
            document.getElementById("loader")['style'].display = "none";
        });

    return true;
}