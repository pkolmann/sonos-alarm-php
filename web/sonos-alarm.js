function showSection(section, alarmId = null) {
    const sections = document.getElementsByClassName("content-section");
    for (let i = 0; i < sections.length; i++) {
        sections[i].classList.remove("active-section");
    }
    document.getElementById(section).classList.add("active-section");

    if (section === 'home') {
        populateHomeSection();
    } else if (section === 'add') {
        populateAddAlarmSection(alarmId);
    }
}

function formatDuration(alarmDuration) {
    if (alarmDuration === null || alarmDuration === undefined) {
        return "N/A";
    }

    // split the duration string into hours, minutes, and seconds
    const parts = alarmDuration.split(':');
    let hours = 0, minutes = 0, seconds = 0;
    if (parts.length === 3) {
        hours = parseInt(parts[0], 10);
        minutes = parseInt(parts[1], 10);
        seconds = parseInt(parts[2], 10);
    } else if (parts.length === 2) {
        minutes = parseInt(parts[0], 10);
        seconds = parseInt(parts[1], 10);
    } else if (parts.length === 1) {
        seconds = parseInt(parts[0], 10);
    }

    // format the duration string
    let formattedDuration = "";
    if (hours > 0) {
        formattedDuration += hours + "h ";
    }
    if (minutes > 0) {
        formattedDuration += minutes + "m ";
    }
    if (seconds > 0 || formattedDuration === "") {
        formattedDuration += seconds + "s";
    }

    return formattedDuration.trim();

}

function populateHomeSection() {
    console.log("Showing home section");
    // activate the loader
    document.getElementById("loader").style.display = "block";
    let url = new URL("api.php", window.location.href);
    url.searchParams.append("cmd", "getAlarms");

    // fetch the content for the section
    fetch(url)
        .then(response => {
            if (!response.ok) {
                let errorMessage = document.getElementById("error-message");
                errorMessage.innerHTML = "Error: " + response.status + " " + response.statusText;
                errorMessage.style.display = "block";
                document.getElementById("loader").style.display = "none";
            }
            return response.text()
        })
        .then(data => {
            let home = document.getElementById('home');

            // remove any existing content
            home.innerHTML = "";

            // parse the response as JSON
            const json = JSON.parse(data);
            console.log("JSON response: ", json);

            // check if there is an error in the response
            if (json.error) {
                let errorMessage = document.getElementById("error-message");
                errorMessage.innerHTML = "Error: " + json.error;
                errorMessage.style.display = "block";
                document.getElementById("loader").style.display = "none";
                return;
            }

            // create a row for the alarms to display
            let row = document.createElement('div');
            row.className = 'row';

            let alarmCount = 0;
            // create a new div for each alarm
            json.forEach(alarm => {
                console.log("Alarm: ", alarm);
                const alarmDiv = document.createElement('div');
                alarmDiv.id = 'alarm-' + alarm.id + '-div';
                alarmDiv.className = 'col-sm-12 col-md-6 col-lg-4 mb-4';

                let alarmIcon = alarm.enabled ? "⏰" : "😴";

                // create the content for the alarm
                alarmDiv.innerHTML = `
                    <div class='card position-relative'>
                    <div id='alarm-${alarm.id}-icon' class='card-icon' onclick='toggleAlarm(${alarm.id})'>${alarmIcon}</div>
                    <div class='card-body'>
                    <h5 class='card-title'>${alarm.time} @ ${alarm['room']}</h5>
                    <p>${alarm['frequencyDescription']}</p>
                    
                    <p><ul>
                        <li id='alarm-${alarm.id}-enabled'><b>Enabled:</b> ${alarm.enabled ? "Active" : "Dormant"}</li>
                        <li><b>Duration:</b> ${formatDuration(alarm['duration'])}</li>
                        <li><b>Music:</b> ${alarm['music']}</li>
                        <li><b>Repeat:</b> ${alarm['repeat'] ? "Repeat" : "Once"}</li>
                        <li><b>Volume:</b> ${alarm['volume']}</li>
                        <li><b>Shuffle:</b> ${alarm['shuffle']}</li>
                    </ul> 
                   
                    <button type='button' class='btn btn-primary' onclick="toggleAlarm(${alarm.id})">Toggle Alarm</button>
                    <button type='button' class='btn btn-primary' onclick="editAlarm(${alarm.id})">Edit</button>
                    <button type='button' class='btn btn-primary' onclick="deleteAlarm(${alarm.id})">Delete</button>
                    </div>
                    </div>
                `;

                row.appendChild(alarmDiv);
                alarmCount++;
                if (alarmCount % 3 === 0) {
                    // add a new row every 3 alarms
                    home.appendChild(row);
                    row = document.createElement('div');
                    row.className = 'row mt-4';
                }
            });

            // append the row to the home section
            home.appendChild(row);

            // hide the loader
            document.getElementById("loader")['style'].display = "none";
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);
            let errorMessage = document.getElementById("error-message");
            errorMessage.innerHTML = "Error: " + error.message;
            errorMessage.style.display = "block";

            // hide the loader
            document.getElementById("loader")['style'].display = "none";
        });
}

function populateAddAlarmSection(alarmId = null) {
    if (alarmId) {
        console.log("Editing alarm with ID: " + alarmId);
        document.getElementById("addAlarmH2").innerText = "Update Alarm";
        document.getElementById("addAlarmBtn").innerText = "Update Alarm";
        document.getElementById("addAlarmBtn").onclick = function() { addAlarm(alarmId); };
    } else {
        console.log("Showing add alarm section");
        document.getElementById("addAlarmH2").innerText = "Add Alarm";
        document.getElementById("addAlarmBtn").innerText = "Add Alarm";
        document.getElementById("addAlarmBtn").onclick = function() { addAlarm(); };
        document.getElementById("time").value = "--:--"; // reset time input
    }

    // activate the loader
    console.log("activating loader for add alarm section");
    document.getElementById("loader").style.display = "block";
    let url = new URL("api.php", window.location.href);
    url.searchParams.append("cmd", "getAlarmDetails");
    if (alarmId) {
        url.searchParams.append("alarmId", alarmId);
    }

    // fetch the content for the section
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.text();
        })
        .then(data => {
            const json = JSON.parse(data);
            const rooms = json['rooms'];
            const music = json['music'];
            const alarms = json['alarms'];
            console.log("Rooms JSON response: ", rooms);
            console.log("Music JSON response: ", music);
            console.log("Alarms JSON response: ", alarms);

            let alarm = null;
            if (alarmId) {
                // find the alarm with the given ID
                alarm = alarms.find(a => a.id === alarmId);
                console.log("Editing alarm: ", alarm);

                if (alarm !== null) {
                    // populate the form fields with the alarm data
                    document.getElementById("time").value = alarm['time'].toString().substring(0,5);
                    document.getElementById("frequency").value = alarm['frequency'];

                    // check if duration in option values is available
                    const durationSelect = document.getElementById("duration");
                    const durationOptions = Array.from(durationSelect.options).map(option => option.value);
                    console.log("Current duration: ", alarm['duration']);
                    console.log("Duration options: ", durationOptions);

                    let durationFound = false;
                    durationOptions.forEach(option => {
                        if (option === alarm['duration'].toString()) {
                            console.log("Duration option found: ", option);
                            durationSelect.value = alarm['duration'].toString();
                            durationFound = true;
                        } else if (parseInt(option) === parseInt(alarm['duration'])) {
                            console.log("Duration option found (parsed): ", option);
                            durationSelect.value = parseInt(alarm['duration']).toString();
                            durationFound = true;
                        }
                    });

                    if (!durationFound) {
                        console.log("Duration option not found, adding: ", alarm['duration']);
                        const newOption = document.createElement("option");
                        newOption.value = alarm['duration'];

                        let durationText = "";
                        if (alarm['duration'] / 3600 >= 1) {
                            durationText += Math.floor(alarm['duration'] / 3600) + " hours ";
                        }
                        if ((alarm['duration'] % 3600) / 60 >= 1) {
                            durationText += Math.floor((alarm['duration'] % 3600) / 60) + " minutes ";
                        }
                        if (alarm['duration'] % 60 > 0 || durationText === "") {
                            durationText += (alarm['duration'] % 60) + " seconds";
                        }

                        newOption.textContent = durationText.trim();
                        newOption.selected = true; // select the new option

                        // insert the new option at the proper position
                        const durationSelect = document.getElementById("duration");
                        const options = Array.from(durationSelect.options);
                        let inserted = false;
                        for (let i = 0; i < options.length; i++) {
                            if (parseInt(options[i].value) > parseInt(alarm['duration'])) {
                                durationSelect.insertBefore(newOption, options[i]);
                                inserted = true;
                                break;
                            }
                        }
                        if (!inserted) {
                            // if no larger value found, append at the end
                            console.log("Appending new duration option at the end: ", newOption);
                            durationSelect.appendChild(newOption);
                        }
                    }
                }
            }

            // populate the room select element
            const roomSelect = document.getElementById("room");
            roomSelect.innerHTML = ""; // clear existing options
            rooms.forEach(room => {
                console.log("Room: ", room);
                const option = document.createElement("option");
                option.value = room['name'];
                option.textContent = room['name'];
                if (alarm && alarm['room'] === room['name']) {
                    option.selected = true; // select the room if editing an alarm
                }
                roomSelect.appendChild(option);
            });

            // populate the music select element
            const musicSelect = document.getElementById("music");
            musicSelect.innerHTML = ""; // clear existing options
            music.forEach(station => {
                console.log("Music station: ", station);
                const option = document.createElement("option");
                option.value = station['uri'];
                option.textContent = station['title'];
                if (alarm && alarm['musicUri'] === station['uri']) {
                    option.selected = true; // select the music if editing an alarm
                }
                musicSelect.appendChild(option);
            });

            // hide the loader
            console.log("hiding loader for add alarm section");
            document.getElementById("loader")['style'].display = "none";
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);
            let errorMessage = document.getElementById("error-message");
            errorMessage.innerHTML = "Error: " + error.message;
            errorMessage.style.display = "block";

            // hide the loader
            console.log("hiding loader for add alarm section due to error");
            document.getElementById("loader")['style'].display = "none";
        });
}

function toggleAlarm(alarmId) {
    console.log("Toggle alarm: " + alarmId);

    // activate the loader
    document.getElementById("loader")['style'].display = "block";
    let url = new URL("api.php", window.location.href);
    url.searchParams.append("cmd", "toggleAlarm");
    url.searchParams.append("alarmId", alarmId);

    fetch(url)
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
    showSection('add', alarmId);
    return true;
}

function deleteAlarm(alarmId) {
    console.log("Deleting alarm: ", alarmId)

    // activate the loader
    document.getElementById("loader")['style'].display = "block";

    let url = new URL("api.php", window.location.href);
    url.searchParams.append("cmd", "deleteAlarm");
    url.searchParams.append("alarmId", alarmId);
    fetch(url)
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

function addAlarm(alarmId = null) {
    const room = document.getElementById("room").valueOf().value;
    const time = document.getElementById("time").valueOf().value;
    const music = document.getElementById("music").valueOf().value;
    const frequency = document.getElementById("frequency").valueOf().value;
    const duration = document.getElementById("duration").valueOf().value;

    console.log("Add alarm: " + room + " " + time + " " + music + " " + frequency + " " + duration);

    // activate the loader
    document.getElementById("loader")['style'].display = "block";

    let url = new URL("api.php", window.location.href);
    url.searchParams.append("cmd", "addAlarm");
    if (alarmId) {
        url.searchParams.append("alarmId", alarmId);
    }
    url.searchParams.append("room", room);
    url.searchParams.append("time", time);
    url.searchParams.append("music", music);
    url.searchParams.append("frequency", frequency);
    url.searchParams.append("duration", duration);
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            showSection('home');
            return response.text();
        })
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error("There has been a problem with your fetch operation:", error);
        });

    return true;
}

// on document ready, show the home section
document.addEventListener("DOMContentLoaded", function() {
    showSection('home');
    document.getElementById("loader")['style'].display = "block"; // show the loader initially
});