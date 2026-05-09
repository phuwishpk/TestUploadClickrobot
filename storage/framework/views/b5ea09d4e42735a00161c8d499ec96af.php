<?php $__env->startSection('title', 'อัปโหลดไฟล์'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">อัปโหลดไฟล์</h1>
    <p class="text-gray-600">อัปโหลดรูปภาพและวีดีโอให้นักเรียน</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form id="upload_form" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน</label>
                <select name="classroom_id" id="classroom_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 <?php $__errorArgs = ['classroom_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    required onchange="loadStudents(this.value)">
                    <option value="">เลือกห้องเรียน</option>
                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($classroom->id); ?>" <?php echo e($selectedClassroom && $selectedClassroom->id == $classroom->id ? 'selected' : ''); ?>>
                            <?php echo e($classroom->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label for="upload_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่อัปโหลด</label>
                <input type="date" name="upload_date" id="upload_date" 
                    value="<?php echo e(old('upload_date', now()->format('Y-m-d'))); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">เลือกนักเรียน</label>
            <div id="student_list" class="border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                <p class="text-gray-500 text-center">กรุณาเลือกห้องเรียนก่อน</p>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                <input type="checkbox" id="select_all" class="mr-1" onchange="toggleSelectAll()">
                <label for="select_all">เลือกทั้งหมด</label>
            </p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">ไฟล์ (รูป/วีดีโอ)</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition cursor-pointer" id="drop_zone">
                <input type="file" name="files[]" id="files" multiple accept="image/*,video/*"
                    class="hidden" onchange="handleFileSelect(this)">
                <label for="files" class="cursor-pointer">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-gray-600">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวาง</p>
                    <p class="text-xs text-gray-400 mt-1">รองรับ: JPG, PNG, GIF, WebP, MP4, MOV, AVI (ไม่เกิน 200MB ต่อไฟล์)</p>
                </label>
            </div>
            <div id="file_preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>

        <!-- Progress Section -->
        <div id="upload_progress_section" class="hidden mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span id="upload_status_text" class="text-sm font-medium text-gray-700">กำลังอัปโหลด...</span>
                    <span id="upload_percentage" class="text-sm font-bold text-indigo-600">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="upload_progress_bar" class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div id="file_status_container" class="mt-3 space-y-2"></div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" id="upload_btn" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition flex items-center gap-2">
                <span>อัปโหลดไฟล์</span>
                <svg id="upload_spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const classrooms = <?php echo json_encode($classrooms, 15, 512) ?>;

function loadStudents(classroomId) {
    const container = document.getElementById('student_list');
    const classroom = classrooms.find(c => c.id == classroomId);
    
    if (!classroom || !classroom.students || classroom.students.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">ไม่มีนักเรียนในห้องนี้</p>';
        return;
    }

    let html = '';
    classroom.students.forEach(student => {
        html += `
            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                <input type="checkbox" name="student_ids[]" value="${student.id}" class="student-checkbox w-4 h-4 text-indigo-600 rounded mr-3">
                <span class="text-sm">${student.code} - ${student.name}</span>
            </label>
        `;
    });
    container.innerHTML = html;
}

function toggleSelectAll() {
    const checked = document.getElementById('select_all').checked;
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = checked);
}

function handleFileSelect(input) {
    const container = document.getElementById('file_preview');
    container.innerHTML = '';
    
    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 rounded p-2 text-sm';
        
        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            div.innerHTML = `<img src="${url}" class="w-full h-24 object-cover rounded mb-1"><span class="truncate">${file.name}</span><span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>`;
        } else {
            div.innerHTML = `<div class="w-full h-24 bg-red-50 rounded flex items-center justify-center mb-1"><span class="text-2xl">🎬</span></div><span class="truncate">${file.name}</span><span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>`;
        }
        
        container.appendChild(div);
    });
}

// Drop zone
const dropZone = document.getElementById('drop_zone');
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
    const input = document.getElementById('files');
    input.files = e.dataTransfer.files;
    handleFileSelect(input);
});

// Upload with Progress Bar - Per Student Upload
document.getElementById('upload_form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const files = document.getElementById('files').files;
    const studentCheckboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
    const classroomId = document.getElementById('classroom_id').value;
    
    // Validation
    if (!classroomId) {
        alert('กรุณาเลือกห้องเรียน');
        return;
    }
    if (studentCheckboxes.length === 0) {
        alert('กรุณาเลือกนักเรียน');
        return;
    }
    if (files.length === 0) {
        alert('กรุณาเลือกไฟล์');
        return;
    }
    
    const studentIds = Array.from(studentCheckboxes).map(cb => cb.value);
    const totalOperations = files.length * studentIds.length;
    let completedOperations = 0;
    
    // UI Elements
    const progressSection = document.getElementById('upload_progress_section');
    const progressBar = document.getElementById('upload_progress_bar');
    const percentage = document.getElementById('upload_percentage');
    const statusText = document.getElementById('upload_status_text');
    const fileStatusContainer = document.getElementById('file_status_container');
    const uploadBtn = document.getElementById('upload_btn');
    const spinner = document.getElementById('upload_spinner');
    
    progressSection.classList.remove('hidden');
    progressBar.style.width = '0%';
    percentage.textContent = '0%';
    statusText.textContent = 'กำลังเตรียมอัปโหลด...';
    fileStatusContainer.innerHTML = '';
    uploadBtn.disabled = true;
    uploadBtn.classList.add('opacity-50', 'cursor-not-allowed');
    spinner.classList.remove('hidden');
    
    // Create status items for each student-file combination
    const statusItems = [];
    let studentName = '';
    studentIds.forEach((studentId, studentIdx) => {
        studentCheckboxes.forEach(cb => {
            if (cb.value === studentId) {
                studentName = cb.closest('label').querySelector('span').textContent;
            }
        });
        Array.from(files).forEach((file, fileIdx) => {
            const itemId = `${studentId}_${fileIdx}`;
            const statusItem = document.createElement('div');
            statusItem.id = `status_${itemId}`;
            statusItem.className = 'flex items-center gap-2 text-sm p-2 bg-white rounded';
            statusItem.innerHTML = `
                <span class="file-icon w-6 h-6 flex items-center justify-center text-gray-400">⏳</span>
                <span class="file-name truncate flex-1 text-xs">${file.name}</span>
                <span class="file-size text-xs text-gray-400">${(file.size/1024).toFixed(1)} KB</span>
                <span class="file-status text-xs px-2 py-1 rounded bg-gray-50">รอ...</span>
            `;
            fileStatusContainer.appendChild(statusItem);
            statusItems.push({ id: itemId, studentId, fileIdx, file });
        });
    });
    
    let hasError = false;
    let processingInterval = null;
    let fakeInterval = null;
    let uploadStartTime = null;
    
    // Calculate estimated total time based on file sizes
    let totalSize = 0;
    Array.from(files).forEach(file => {
        totalSize += file.size;
    });
    // Estimate: 1MB = 1 second total, minimum 5 seconds, maximum 60 seconds
    const estimatedTotalTime = Math.min(60000, Math.max(5000, totalSize / (1024 * 1024) * 1000));
    
    // Upload files using XHR with fake progress tracking
    try {
        const uploadXhr = new XMLHttpRequest();
        
        // Show initial state
        statusText.textContent = 'กำลังอัปโหลดไฟล์ไปยังเซิร์ฟเวอร์...';
        uploadStartTime = Date.now();
        
        // Start fake progress immediately (0→100)
        let fakePercent = 0;
        const fakeIncrement = 100 / (estimatedTotalTime / 50); // smooth increment
        
        fakeInterval = setInterval(() => {
            fakePercent += fakeIncrement;
            if (fakePercent > 100) fakePercent = 100;
            progressBar.style.width = Math.round(fakePercent) + '%';
            percentage.textContent = Math.round(fakePercent) + '%';
            statusText.textContent = `อัปโหลด ${Math.round(fakePercent)}%`;
        }, 50);
        
        // Helper function to accelerate to target
        function accelerateTo(target, duration, callback) {
            // If already at or past target, skip animation
            if (fakePercent >= target) {
                if (callback) callback();
                return;
            }
            const start = fakePercent;
            const startTime = Date.now();
            console.log('accelerateTo called', {target, start, duration, fakePercent});
            const accelInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                fakePercent = start + (target - start) * progress;
                progressBar.style.width = Math.round(fakePercent) + '%';
                percentage.textContent = Math.round(fakePercent) + '%';
                if (progress >= 1) {
                    clearInterval(accelInterval);
                    if (callback) callback();
                }
            }, 16);
        }
        
        // When upload completes (real)
        uploadXhr.addEventListener('loadend', function() {
            console.log('loadend fired', {fakePercent, status: uploadXhr.status});
            if (uploadXhr.status >= 200 && uploadXhr.status < 300) {
                // Stop fake interval first before accelerating
                if (fakeInterval) {
                    clearInterval(fakeInterval);
                    fakeInterval = null;
                }
                
                // Accelerate to 40% quickly (300ms)
                accelerateTo(40, 300, function() {
                    // Start processing phase
                    statusText.textContent = 'กำลังประมวลผล...';
                    
                    // Update status for each file
                    studentIds.forEach((studentId) => {
                        Array.from(files).forEach((file, fIdx) => {
                            const itemId = `${studentId}_${fIdx}`;
                            const statusEl = document.getElementById(`status_${itemId}`);
                            if (statusEl) {
                                const itemIcon = statusEl.querySelector('.file-icon');
                                const itemStatus = statusEl.querySelector('.file-status');
                                if (itemIcon) {
                                    itemIcon.textContent = '⚙️';
                                    itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-blue-500 animate-spin';
                                }
                                if (itemStatus) {
                                    itemStatus.textContent = 'กำลังประมวลผล';
                                    itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-blue-100 text-blue-700';
                                }
                            }
                        });
                    });
                    
                    // Continue fake progress (already running, will reach 90%)
                    // When fake reaches ~90%, processing should be done
                    console.log('processing timeout starting', {fakePercent});
                    setTimeout(() => {
                        // Accelerate to 100%
                        accelerateTo(100, 500, function() {
                            // Update all status items to complete
                            studentIds.forEach((studentId) => {
                                Array.from(files).forEach((file, fIdx) => {
                                    const itemId = `${studentId}_${fIdx}`;
                                    const statusEl = document.getElementById(`status_${itemId}`);
                                    if (statusEl) {
                                        const itemIcon = statusEl.querySelector('.file-icon');
                                        const itemStatus = statusEl.querySelector('.file-status');
                                        if (itemIcon) {
                                            itemIcon.textContent = '✅';
                                            itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-green-500';
                                        }
                                        if (itemStatus) {
                                            itemStatus.textContent = 'เสร็จ';
                                            itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-green-100 text-green-700';
                                        }
                                    }
                                });
                            });
                            
                            // Parse response
                            try {
                                const response = JSON.parse(uploadXhr.responseText);
                                if (response.errors && response.errors.length > 0) {
                                    hasError = true;
                                    statusText.textContent = 'อัปโหลดเสร็จบางส่วน (มีข้อผิดพลาด ' + response.errors.length + ' รายการ)';
                                    statusText.className = 'text-sm font-medium text-orange-600';
                                } else {
                                    statusText.textContent = 'อัปโหลดเสร็จสิ้น!';
                                    statusText.className = 'text-sm font-medium text-green-600';
                                }
                            } catch (e) {
                                statusText.textContent = 'อัปโหลดเสร็จสิ้น!';
                                statusText.className = 'text-sm font-medium text-green-600';
                            }
                            
                            spinner.classList.add('hidden');
                            uploadBtn.disabled = false;
                            uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            
                            if (!hasError) {
                                setTimeout(() => {
                                    window.location.href = '<?php echo e(route("teacher.dashboard")); ?>';
                                }, 1500);
                            }
                        });
                    }, 500); // Processing delay
                });
            } else {
                // Upload failed
                hasError = true;
                if (fakeInterval) clearInterval(fakeInterval);
                statusText.textContent = 'อัปโหลดล้มเหลว';
                statusText.className = 'text-sm font-medium text-red-600';
                spinner.classList.add('hidden');
                uploadBtn.disabled = false;
                uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Update all status items to error
                studentIds.forEach((studentId) => {
                    Array.from(files).forEach((file, fIdx) => {
                        const itemId = `${studentId}_${fIdx}`;
                        const statusEl = document.getElementById(`status_${itemId}`);
                        if (statusEl) {
                            const itemIcon = statusEl.querySelector('.file-icon');
                            const itemStatus = statusEl.querySelector('.file-status');
                            if (itemIcon) {
                                itemIcon.textContent = '❌';
                                itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-red-500';
                            }
                            if (itemStatus) {
                                itemStatus.textContent = 'ผิดพลาด';
                                itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-red-100 text-red-700';
                            }
                        }
                    });
                });
            }
        });
        
        uploadXhr.addEventListener('error', function() {
            hasError = true;
            if (fakeInterval) clearInterval(fakeInterval);
            statusText.textContent = 'การเชื่อมต่อผิดพลาด';
            statusText.className = 'text-sm font-medium text-red-600';
            spinner.classList.add('hidden');
            uploadBtn.disabled = false;
            uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        
        uploadXhr.open('POST', '<?php echo e(route("teacher.upload.store")); ?>');
        uploadXhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        uploadXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        uploadXhr.timeout = 600000;
        uploadXhr.send(formData);
        
    } catch (error) {
        hasError = true;
        if (fakeInterval) clearInterval(fakeInterval);
        statusText.textContent = 'เกิดข้อผิดพลาด: ' + error.message;
        statusText.className = 'text-sm font-medium text-red-600';
        spinner.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
});

// Load students if classroom already selected
<?php if($selectedClassroom): ?>
loadStudents(<?php echo e($selectedClassroom->id); ?>);
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/upload.blade.php ENDPATH**/ ?>