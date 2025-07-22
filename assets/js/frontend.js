/**
 * Institute Management Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initInstituteManagement();
    });
    
    /**
     * Initialize plugin functionality
     */
    function initInstituteManagement() {
        initSearch();
        initFilters();
        initLazyLoading();
    }
    
    /**
     * Initialize search functionality
     */
    function initSearch() {
        const searchInput = $('#institute-search');
        const searchBtn = $('#institute-search-btn');
        
        if (searchInput.length) {
            // Live search with debounce
            let searchTimeout;
            searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performSearch(searchInput.val());
                }, 300);
            });
            
            // Search button click
            searchBtn.on('click', function() {
                performSearch(searchInput.val());
            });
            
            // Enter key search
            searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performSearch($(this).val());
                }
            });
        }
    }
    
    /**
     * Initialize filter functionality
     */
    function initFilters() {
        const typeFilter = $('#institute-type-filter');
        const classFilter = $('#institute-class-filter');
        
        if (typeFilter.length) {
            typeFilter.on('change', function() {
                applyFilters();
            });
        }
        
        if (classFilter.length) {
            classFilter.on('change', function() {
                applyFilters();
            });
        }
    }
    
    /**
     * Initialize lazy loading for images
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    /**
     * Perform search
     */
    function performSearch(query) {
        const resultsContainer = $('#institute-directory-results');
        
        if (!resultsContainer.length) return;
        
        // Show loading state
        resultsContainer.html('<div class="institute-loading"></div>');
        
        // AJAX search request
        $.ajax({
            url: institute_management_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'institute_search',
                query: query,
                nonce: institute_management_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data);
                } else {
                    resultsContainer.html('<p class="institute-no-results">' + 
                        institute_management_ajax.strings.error + '</p>');
                }
            },
            error: function() {
                resultsContainer.html('<p class="institute-no-results">' + 
                    institute_management_ajax.strings.error + '</p>');
            }
        });
    }
    
    /**
     * Apply filters
     */
    function applyFilters() {
        const typeFilter = $('#institute-type-filter').val();
        const classFilter = $('#institute-class-filter').val();
        const resultsContainer = $('#institute-directory-results');
        
        if (!resultsContainer.length) return;
        
        // Show loading state
        resultsContainer.html('<div class="institute-loading"></div>');
        
        // AJAX filter request
        $.ajax({
            url: institute_management_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'institute_filter',
                type: typeFilter,
                class: classFilter,
                nonce: institute_management_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data);
                } else {
                    resultsContainer.html('<p class="institute-no-results">' + 
                        institute_management_ajax.strings.error + '</p>');
                }
            },
            error: function() {
                resultsContainer.html('<p class="institute-no-results">' + 
                    institute_management_ajax.strings.error + '</p>');
            }
        });
    }
    
})(jQuery); 