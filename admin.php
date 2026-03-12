<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Load current cameras
        $camerasFile = 'cameras.json';
        $cameras = json_decode(file_get_contents($camerasFile), true);

        if ($action === 'save') {
            // Get the cameras data from POST
            $updatedCameras = json_decode($_POST['cameras_data'], true);

            if ($updatedCameras === null) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                exit;
            }

            // Validate each camera has required fields
            foreach ($updatedCameras as $camera) {
                if (empty($camera['name']) || empty($camera['cam_ip']) || empty($camera['cam_key']) ||
                    empty($camera['caller_srt']) || empty($camera['port'])) {
                    echo json_encode(['success' => false, 'error' => 'All fields are required for each camera']);
                    exit;
                }
            }

            // Save to file with pretty formatting
            $json = json_encode($updatedCameras, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($camerasFile, $json) !== false) {
                echo json_encode(['success' => true, 'message' => 'Cameras saved successfully!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to write to cameras.json']);
            }
            exit;
        }
    }
}

// Load cameras for display
$cameras = json_decode(file_get_contents('cameras.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Configuration Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-slate-900 p-5">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold text-white drop-shadow-lg">Camera Configuration Admin</h1>
            <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                ← Back to Control Panel
            </a>
        </div>



        <!-- Status Message (Fixed at top) -->
        <div id="status-message" class="hidden fixed top-5 left-1/2 transform -translate-x-1/2 p-4 rounded-lg shadow-2xl z-50 min-w-96 text-center"></div>

        <!-- Camera Forms -->
        <div id="cameras-container" class="space-y-4 mb-6">
            <!-- Camera cards will be inserted here by JavaScript -->
        </div>

        <!-- Add Camera Button -->
        <div class="flex gap-3 mb-6">
            <button onclick="addCamera()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Camera
            </button>
        </div>

        <!-- Save Button (Fixed at bottom) -->
        <div class="sticky bottom-5 flex justify-center">
            <button onclick="saveCameras()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-12 rounded-lg shadow-2xl transition-all transform hover:scale-105 flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Save All Changes
            </button>
        </div>
    </div>

    <script>
        // Load initial camera data
        let cameras = <?= json_encode($cameras) ?>;

        // Render all cameras
        function renderCameras() {
            const container = document.getElementById('cameras-container');
            container.innerHTML = '';

            cameras.forEach((camera, index) => {
                const card = createCameraCard(camera, index);
                container.appendChild(card);
            });
        }

        // Create a camera card
        function createCameraCard(camera, index) {
            const div = document.createElement('div');
            div.className = 'bg-gray-800 rounded-xl p-6 shadow-xl border border-gray-700';
            div.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Camera ${index + 1}</h3>
                    <button onclick="deleteCamera(${index})" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition-colors text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Court Name</label>
                        <input type="text"
                               value="${camera.name}"
                               onchange="updateCamera(${index}, 'name', this.value)"
                               class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Court 1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Camera IP</label>
                        <input type="text"
                               value="${camera.cam_ip}"
                               onchange="updateCamera(${index}, 'cam_ip', this.value)"
                               class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="158.104.114.101">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Camera Key</label>
                        <input type="text"
                               value="${camera.cam_key}"
                               onchange="updateCamera(${index}, 'cam_key', this.value)"
                               class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="236350860">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">SRT Caller IP</label>
                        <input type="text"
                               value="${camera.caller_srt}"
                               onchange="updateCamera(${index}, 'caller_srt', this.value)"
                               class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="158.104.114.33">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Port</label>
                        <input type="text"
                               value="${camera.port}"
                               onchange="updateCamera(${index}, 'port', this.value)"
                               class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="5001">
                    </div>
                </div>
            `;
            return div;
        }

        // Update a camera field
        function updateCamera(index, field, value) {
            cameras[index][field] = value;
        }

        // Add a new camera
        function addCamera() {
            const newCamera = {
                name: `Court ${cameras.length + 1}`,
                cam_ip: '158.104.114.1XX',
                cam_key: '000000000',
                caller_srt: '158.104.114.33',
                port: `500${cameras.length + 1}`
            };
            cameras.push(newCamera);
            renderCameras();

            // Scroll to the new camera
            setTimeout(() => {
                const container = document.getElementById('cameras-container');
                container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }

        // Delete a camera
        function deleteCamera(index) {
            if (confirm(`Are you sure you want to delete ${cameras[index].name}?`)) {
                cameras.splice(index, 1);
                renderCameras();
                showStatus('Camera deleted. Remember to save changes!', 'warning');
            }
        }

        // Save cameras to JSON file
        async function saveCameras() {
            // Validate all cameras have required fields
            for (let i = 0; i < cameras.length; i++) {
                const camera = cameras[i];
                if (!camera.name || !camera.cam_ip || !camera.cam_key || !camera.caller_srt || !camera.port) {
                    showStatus(`Error: Camera ${i + 1} is missing required fields!`, 'error');
                    return;
                }
            }

            try {
                const formData = new FormData();
                formData.append('action', 'save');
                formData.append('cameras_data', JSON.stringify(cameras));

                const response = await fetch('admin.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStatus(result.message, 'success');
                } else {
                    showStatus(`Error: ${result.error}`, 'error');
                }
            } catch (error) {
                showStatus(`Error: ${error.message}`, 'error');
            }
        }

        // Show status message
        function showStatus(message, type) {
            const statusEl = document.getElementById('status-message');
            statusEl.className = 'fixed top-5 left-1/2 transform -translate-x-1/2 p-4 rounded-lg shadow-2xl z-50 min-w-96 text-center font-semibold transition-all duration-300';

            if (type === 'success') {
                statusEl.className += ' bg-green-900 text-green-300 border-2 border-green-700';
            } else if (type === 'error') {
                statusEl.className += ' bg-red-900 text-red-300 border-2 border-red-700';
            } else if (type === 'warning') {
                statusEl.className += ' bg-yellow-900 text-yellow-300 border-2 border-yellow-700';
            }

            statusEl.textContent = message;
            statusEl.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                statusEl.classList.add('hidden');
            }, 5000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            renderCameras();
        });
    </script>
</body>
</html>
