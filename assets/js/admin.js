/**
 * Institute Management Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initAdminFeatures();
    });
    
    /**
     * Initialize admin features
     */
    function initAdminFeatures() {
        initFormValidation();
        initBulkActions();
        initQuickEdit();
        initFileUpload();
    }
    
    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // Validate required fields
        $('form').on('submit', function(e) {
            let isValid = true;
            
            $(this).find('input[required], select[required]').each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert(institute_management_admin.strings.error);
            }
        });
        
        // Remove error class on input
        $('input, select').on('input change', function() {
            $(this).removeClass('error');
        });
    }
    
    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        $('.bulkactions select').on('change', function() {
            const action = $(this).val();
            if (action.includes('delete')) {
                $(this).closest('form').on('submit', function(e) {
                    if (!confirm(institute_management_admin.strings.confirm_delete)) {
                        e.preventDefault();
                    }
                });
            }
        });
    }
    
    /**
     * Initialize quick edit functionality
     */
    function initQuickEdit() {
        $('.editinline').on('click', function() {
            // Auto-populate quick edit fields
            const postId = $(this).closest('tr').attr('id').replace('post-', '');
            const row = $('#post-' + postId);
            
            // Get current values
            const title = row.find('.column-title strong a').text();
            const status = row.find('.column-student_status, .column-staff_status').text();
            
            // Set quick edit values
            setTimeout(function() {
                $('#edit-' + postId + ' input[name="post_title"]').val(title);
            }, 100);
        });
    }
    
    /**
     * Initialize file upload functionality
     */
    function initFileUpload() {
        const uploadArea = $('.institute-file-upload');
        
        if (uploadArea.length) {
            // Drag and drop
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });
            
            // File input change
            uploadArea.find('input[type="file"]').on('change', function() {
                if (this.files.length > 0) {
                    handleFileUpload(this.files[0]);
                }
            });
        }
    }
    
    /**
     * Handle file upload
     */
    function handleFileUpload(file) {
        // Validate file type
        if (!file.name.toLowerCase().endsWith('.csv')) {
            alert('Please select a CSV file.');
            return;
        }
        
        // Show upload progress
        const progressHtml = '<div class="institute-upload-progress">' +
            '<div class="progress-bar"><div class="progress-fill"></div></div>' +
            '<span class="progress-text">Uploading...</span>' +
            '</div>';
        
        $('.institute-file-upload').html(progressHtml);
        
        // Create FormData
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'institute_import_csv');
        formData.append('nonce', institute_management_admin.nonce);
        
        // Upload file
        $.ajax({
            url: institute_management_admin.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        $('.progress-fill').css('width', percentComplete + '%');
                        $('.progress-text').text(Math.round(percentComplete) + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $('.institute-file-upload').html('<div class="institute-notice institute-notice-success">' +
                        'File uploaded successfully! ' + response.data.message +
                        '</div>');
                } else {
                    $('.institute-file-upload').html('<div class="institute-notice institute-notice-error">' +
                        'Upload failed: ' + response.data +
                        '</div>');
                }
            },
            error: function() {
                $('.institute-file-upload').html('<div class="institute-notice institute-notice-error">' +
                    'Upload failed. Please try again.' +
                    '</div>');
            }
        });
    }
    
    /**
     * Initialize charts if Chart.js is available
     */
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
    
    function initCharts() {
        // Student/Staff overview chart
        const overviewCanvas = $('#institute-overview-chart');
        if (overviewCanvas.length) {
            new Chart(overviewCanvas[0].getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Students', 'Staff'],
                    datasets: [{
                        data: [
                            overviewCanvas.data('students') || 0,
                            overviewCanvas.data('staff') || 0
                        ],
                        backgroundColor: ['#2563eb', '#7c3aed']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
    
})(jQuery); 