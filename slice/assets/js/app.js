$(document).ready(function () {
    // --- Dark Mode Toggle ---
    const themeBtn = $('#theme-toggle');
    
    // Initial check
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        $('html').addClass('dark');
        themeBtn.html('<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>');
    } else {
        themeBtn.html('<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>');
    }

    themeBtn.on('click', function () {
        $('html').toggleClass('dark');
        if ($('html').hasClass('dark')) {
            localStorage.setItem('color-theme', 'dark');
            $(this).html('<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>');
        } else {
            localStorage.setItem('color-theme', 'light');
            $(this).html('<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>');
        }
    });

    // --- Mobile Menu Toggle ---
    const mobileMenu = $('#mobile-menu');
    const menuOverlay = $('#menu-overlay');

    $('#mobile-menu-toggle').on('click', function () {
        mobileMenu.removeClass('translate-x-full');
        menuOverlay.removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });

    $('#close-menu, #menu-overlay').on('click', function () {
        mobileMenu.addClass('translate-x-full');
        menuOverlay.addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    // --- Search Overlay ---
    const searchOverlay = $('#search-overlay');
    $('#search-toggle, #search-mobile-toggle').on('click', function(e) {
        e.preventDefault();
        searchOverlay.removeClass('hidden').hide().fadeIn(300);
        setTimeout(() => $('#search-input').focus(), 300);
        $('body').addClass('overflow-hidden');
    });

    $('#search-close').on('click', function() {
        searchOverlay.fadeOut(300, function() {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        });
    });

    // --- Lazy Loading ---
    const lazyImages = $('.lazy-image');
    if ('IntersectionObserver' in window) {
        let lazyObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let img = $(entry.target);
                    let src = img.data('src');
                    if (src) {
                        img.attr('src', src);
                        img.on('load', function() {
                            $(this).addClass('loaded');
                        });
                        img.removeAttr('data-src');
                    }
                    lazyObserver.unobserve(entry.target);
                }
            });
        });
        lazyImages.each(function() {
            lazyObserver.observe(this);
        });
    } else {
        lazyImages.each(function() {
            $(this).attr('src', $(this).data('src')).addClass('loaded');
        });
    }

    // --- Reading Progress ---
    const progressBar = $('#reading-progress-bar');
    if (progressBar.length) {
        $(window).on('scroll', function() {
            let s = $(window).scrollTop(),
                d = $(document).height(),
                c = $(window).height();
            let scrollPercent = (s / (d-c)) * 100;
            progressBar.css('width', scrollPercent + '%');
        });
    }

    // --- Font Size Controls ---
    const contentText = $('.article-content');
    if (contentText.length) {
        let currentSize = 18;
        contentText.css('font-size', currentSize + 'px');

        $('#font-increase').on('click', function() {
            if(currentSize < 26) {
                currentSize += 2;
                contentText.css('font-size', currentSize + 'px');
            }
        });
        $('#font-decrease').on('click', function() {
            if(currentSize > 14) {
                currentSize -= 2;
                contentText.css('font-size', currentSize + 'px');
            }
        });
    }

    // --- Infinite Scroll (Homepage Demo) ---
    const infiniteContainer = $('#infinite-scroll-container');
    if (infiniteContainer.length) {
        let isLoading = false;
        let mockCounter = 0; // limit fetching to 3 times in prototype
        
        $(window).on('scroll', function() {
            // Check if user is near bottom
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
                if (!isLoading && mockCounter < 3) {
                    isLoading = true;
                    $('#loading-indicator').removeClass('hidden');

                    // Mock API call delay
                    setTimeout(function() {
                        const template = `
                            <article class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden group cursor-pointer border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-smooth shadow-sm hover:shadow-xl">
                                <div class="relative aspect-video overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1485846234645-a62644f84728?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="سينما" class="w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500">
                                    <div class="absolute top-4 right-4 bg-primary text-slate-900 text-xs font-bold px-3 py-1 rounded-full shadow-md">تحليل</div>
                                </div>
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-primary transition-colors leading-tight">مقال جديد محمل ديناميكياً باستخدام التمرير اللانهائي</h3>
                                    <p class="text-slate-600 dark:text-slate-400 text-sm mb-5 line-clamp-2 leading-relaxed">
                                        هذا النص يمثل محتوى المقال الذي يتم جلبه تلقائياً عند وصول المستخدم إلى أسفل الصفحة عبر سكريبت الـ jQuery المخصص لإعطاء انطباع حيوي للمجلة.
                                    </p>
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium border-t border-slate-100 dark:border-slate-700 pt-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-600"></div>
                                            <span>فريق مذاق السينما</span>
                                        </div>
                                        <span>الآن</span>
                                    </div>
                                </div>
                            </article>
                        `;

                        // Append twice to show more items
                        infiniteContainer.append(template).append(template);
                        $('#loading-indicator').addClass('hidden');
                        isLoading = false;
                        mockCounter++;
                        
                        if(mockCounter >= 3) {
                            infiniteContainer.after('<div class="text-center text-slate-500 dark:text-slate-400 py-8 text-sm">تم الوصول إلى نهاية المقالات.</div>');
                        }
                    }, 1200);
                }
            }
        });
    }

    // --- Archive Filters ---
    const filterBtns = $('.filter-btn');
    if (filterBtns.length) {
        filterBtns.on('click', function() {
            // Reset state
            filterBtns.removeClass('bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-md').addClass('bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700');
            
            // Set active state
            $(this).removeClass('bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700').addClass('bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-md border-transparent');
            
            let filterVal = $(this).data('filter');
            let items = $('.archive-item');

            items.fadeOut(200).promise().done(function() {
                if (filterVal === 'all') {
                    items.fadeIn(300);
                } else {
                    $('.archive-item[data-category="'+filterVal+'"]').fadeIn(300);
                }
            });
        });
    }
});
