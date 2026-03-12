<?php
// Load cameras
$cameras = json_decode(file_get_contents('cameras.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tennis Camera SRT Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status { display: none; }
        .status.success, .status.error, .status.loading { display: block; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-slate-900 p-5">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex-1"></div>
            <h1 class="text-5xl font-bold text-white text-center drop-shadow-lg">Tennis Camera SRT Control</h1>
            <div class="flex-1 flex justify-end">
                <a href="admin.php" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Admin
                </a>
            </div>
        </div>

        <div class="flex justify-center gap-3 mb-8">
            <button onclick="toggleAllSRT('enable')"
                    id="enable-all-btn"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg uppercase tracking-wide transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Enable All
            </button>
            <button onclick="toggleAllSRT('disable')"
                    id="disable-all-btn"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg uppercase tracking-wide transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Disable All
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($cameras as $index => $camera): ?>
                <div class="bg-gray-800 rounded-xl p-6 shadow-xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-200 border border-gray-700">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-2xl font-bold text-white"><?= htmlspecialchars($camera['name']) ?></div>
                        <span id="status-badge-<?= $index ?>" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-700 text-gray-300">
                            Checking...
                        </span>
                    </div>
                    <div class="text-sm text-gray-400 mb-4">
                        Camera: <?= htmlspecialchars($camera['cam_ip']) ?><br>
                        SRT Caller: <?= htmlspecialchars($camera['caller_srt']) ?>:<?= htmlspecialchars($camera['port']) ?>
                    </div>

                    <a href="http://<?= htmlspecialchars($camera['cam_ip']) ?>"
                       target="_blank"
                       class="inline-flex items-center text-sm text-blue-400 hover:text-blue-300 mb-4 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Camera Settings
                    </a>

                    <div class="flex gap-2">
                        <button class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded text-sm uppercase tracking-wide transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="toggleSRT('<?= htmlspecialchars($camera['cam_ip']) ?>',
                                                   '<?= htmlspecialchars($camera['cam_key']) ?>',
                                                   '<?= htmlspecialchars($camera['caller_srt']) ?>',
                                                   '<?= htmlspecialchars($camera['port']) ?>',
                                                   'enable', <?= $index ?>,
                                                   <?= (int)($camera['latency'] ?? 500) ?>)">
                            Enable
                        </button>
                        <button class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-3 rounded text-sm uppercase tracking-wide transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="toggleSRT('<?= htmlspecialchars($camera['cam_ip']) ?>',
                                                   '<?= htmlspecialchars($camera['cam_key']) ?>',
                                                   '<?= htmlspecialchars($camera['caller_srt']) ?>',
                                                   '<?= htmlspecialchars($camera['port']) ?>',
                                                   'disable', <?= $index ?>,
                                                   <?= (int)($camera['latency'] ?? 500) ?>)">
                            Disable
                        </button>
                    </div>

                    <div class="status mt-4 p-3 rounded-lg text-sm text-center" id="status-<?= $index ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Update status badge
        function updateStatusBadge(index, enabled) {
            const badge = document.getElementById(`status-badge-${index}`);
            if (enabled === true) {
                badge.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-900 text-green-300 border border-green-700';
                badge.textContent = '● Enabled';
            } else if (enabled === false) {
                badge.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-gray-700 text-gray-300 border border-gray-600';
                badge.textContent = '○ Disabled';
            } else {
                badge.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-yellow-900 text-yellow-300 border border-yellow-700';
                badge.textContent = '? Unknown';
            }
        }

        // Check status for a camera
        async function checkStatus(cameraIp, camKey, index) {
            try {
                const response = await fetch(`api.php?check_status=1&camera_ip=${encodeURIComponent(cameraIp)}&cam_key=${encodeURIComponent(camKey)}`);
                const result = await response.json();

                // Debug logging
                console.log(`Camera ${cameraIp} status:`, result);

                if (result.success && result.enabled !== null) {
                    updateStatusBadge(index, result.enabled);
                } else {
                    // Log debug info if status is unknown
                    if (result.debug) {
                        console.warn(`Unknown status for camera ${cameraIp}. Response:`, result.debug);
                    }
                    updateStatusBadge(index, null);
                }
            } catch (error) {
                console.error(`Error checking status for camera ${cameraIp}:`, error);
                updateStatusBadge(index, null);
            }
        }

        // Toggle SRT
        async function toggleSRT(cameraIp, camKey, srtIp, port, action, index, latency = 500) {
            const statusEl = document.getElementById(`status-${index}`);
            const card = statusEl.closest('div').closest('div');
            const buttons = card.querySelectorAll('button');

            // Disable buttons
            buttons.forEach(btn => btn.disabled = true);

            // Show loading status
            statusEl.className = 'status loading mt-4 p-3 rounded-lg text-sm text-center bg-blue-900 text-blue-300 border border-blue-700';
            statusEl.textContent = `${action === 'enable' ? 'Enabling' : 'Disabling'} SRT...`;

            try {
                const formData = new FormData();
                formData.append('camera_ip', cameraIp);
                formData.append('cam_key', camKey);
                formData.append('srt_ip', srtIp);
                formData.append('port', port);
                formData.append('latency', latency);
                formData.append('action', action);

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    statusEl.className = 'status success mt-4 p-3 rounded-lg text-sm text-center bg-green-900 text-green-300 border border-green-700';
                    statusEl.textContent = `✓ SRT ${action === 'enable' ? 'enabled' : 'disabled'} successfully!`;

                    // Update status badge
                    updateStatusBadge(index, action === 'enable');
                } else {
                    statusEl.className = 'status error mt-4 p-3 rounded-lg text-sm text-center bg-red-900 text-red-300 border border-red-700';
                    statusEl.textContent = `✗ Error: ${result.error || 'Unknown error'}`;
                }

                // Hide status after 3 seconds
                setTimeout(() => {
                    statusEl.style.display = 'none';
                }, 3000);

            } catch (error) {
                statusEl.className = 'status error mt-4 p-3 rounded-lg text-sm text-center bg-red-900 text-red-300 border border-red-700';
                statusEl.textContent = `✗ Error: ${error.message}`;
            } finally {
                // Re-enable buttons
                buttons.forEach(btn => btn.disabled = false);
            }
        }

        // Toggle all cameras at once
        async function toggleAllSRT(action) {
            const cameras = <?= json_encode($cameras) ?>;
            const enableAllBtn = document.getElementById('enable-all-btn');
            const disableAllBtn = document.getElementById('disable-all-btn');

            // Disable both buttons
            enableAllBtn.disabled = true;
            disableAllBtn.disabled = true;

            // Toggle each camera sequentially
            for (let i = 0; i < cameras.length; i++) {
                const camera = cameras[i];
                await toggleSRT(camera.cam_ip, camera.cam_key, camera.caller_srt, camera.port, action, i, camera.latency ?? 500);
                // Small delay between cameras to avoid overwhelming the network
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            // Re-enable buttons
            enableAllBtn.disabled = false;
            disableAllBtn.disabled = false;
        }

        // Check all camera statuses
        function checkAllStatuses() {
            const cameras = <?= json_encode($cameras) ?>;
            cameras.forEach((camera, index) => {
                checkStatus(camera.cam_ip, camera.cam_key, index);
            });
        }

        // Check statuses on page load
        document.addEventListener('DOMContentLoaded', () => {
            checkAllStatuses();

            // Poll status every 5 seconds
            setInterval(checkAllStatuses, 5000);
        });
    </script>
</body>
</html>

