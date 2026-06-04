<?php get_header(); ?>

<main id="main-content" class="delight-404 min-h-[80vh] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 delight-404__vignette"></div>
    <div class="absolute inset-0 delight-404__grain" aria-hidden="true"></div>
    <canvas id="projector-canvas" class="absolute inset-0 pointer-events-none z-0 opacity-60" aria-hidden="true"></canvas>

    <div class="relative z-10 w-full px-6 max-w-5xl mx-auto">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Film perforation top -->
            <div class="delight-404__sprocket" aria-hidden="true"></div>

            <div class="delight-404__card">
                <span class="delight-404__label">مشهد محذوف</span>
                <h1 class="delight-404__code">404</h1>
                <p class="delight-404__lead">المخرج قرر استبعاد هذا المشهد من النسخة النهائية.</p>
                <p class="delight-404__sub">الصفحة غير موجودة في الأرشيف.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-404__cta">
                    <span>العودة للعرض</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
            </div>

            <!-- Film perforation bottom -->
            <div class="delight-404__sprocket delight-404__sprocket--bottom" aria-hidden="true"></div>

            <?php get_template_part('template-parts/ads/ad-404'); ?>
        </div>

        <?php $popular = mazaq_get_most_read_posts(5); ?>
        <?php if ($popular->have_posts()) : ?>
            <section class="delight-404__popular" aria-labelledby="popular-404-title">
                <h2 id="popular-404-title" class="sr-only">الأكثر قراءة هذا الأسبوع</h2>
                <div class="popular-strip" tabindex="0" aria-label="<?php esc_attr_e('مقالات رائجة يمكن الانتقال إليها', 'mazaq'); ?>">
                    <?php $rank = 1; ?>
                    <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                        <article class="popular-strip__item">
                            <a href="<?php the_permalink(); ?>" class="popular-strip__link group">
                                <span class="popular-strip__rank num"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                                <h3 class="popular-strip__title"><?php the_title(); ?></h3>
                                <span class="popular-strip__meta num"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> مشاهدة</span>
                            </a>
                        </article>
                        <?php $rank++; ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
(function() {
    // Respect user preference for reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const canvas = document.getElementById('projector-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const container = document.getElementById('main-content');
    if (!container) return;

    let width = canvas.width = container.clientWidth;
    let height = canvas.height = container.clientHeight;

    // Handle viewport resize dynamically
    window.addEventListener('resize', () => {
        width = canvas.width = container.clientWidth;
        height = canvas.height = container.clientHeight;
    });

    // Tracking variables for smooth mouse parallax
    let mouseX = width / 2;
    let mouseY = height / 2;
    let targetMouseX = width / 2;
    let targetMouseY = height / 2;

    container.addEventListener('mousemove', (e) => {
        const rect = container.getBoundingClientRect();
        targetMouseX = e.clientX - rect.left;
        targetMouseY = e.clientY - rect.top;
    });

    container.addEventListener('mouseleave', () => {
        targetMouseX = width / 2;
        targetMouseY = height / 2;
    });

    // Dust particles system
    const particleCount = 35;
    const particles = [];

    class Particle {
        constructor() {
            this.reset();
            this.y = Math.random() * height; // Distribute vertically initially
        }

        reset() {
            this.x = Math.random() * width;
            this.y = -20;
            this.vx = (Math.random() - 0.5) * 0.35;
            this.vy = 0.4 + Math.random() * 0.55;
            this.size = 0.5 + Math.random() * 1.5;
            this.opacity = 0;
            this.maxOpacity = 0.15 + Math.random() * 0.3;
            this.flickerSpeed = 0.01 + Math.random() * 0.025;
            this.flickerTime = Math.random() * 100;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            // Add organic horizontal drifting turbulence
            this.vx += (Math.random() - 0.5) * 0.04;
            this.vx = Math.max(-0.5, Math.min(0.5, this.vx));

            this.flickerTime += this.flickerSpeed;

            if (this.y > height + 20 || this.x < -20 || this.x > width + 20) {
                this.reset();
            }
        }

        draw(beamIntensity) {
            // Source coordinates of projection light source
            const sourceX = width / 2 + (mouseX - width / 2) * 0.12;
            const sourceY = -50;
            
            // Distance and angle metrics to source
            const dx = this.x - sourceX;
            const dy = this.y - sourceY;
            const angle = Math.atan2(dy, dx);

            // Target focal point of projection beam
            const targetX = width / 2 + (mouseX - width / 2) * 0.55;
            const targetY = height * 0.55 + (mouseY - height / 2) * 0.35;
            const beamAngle = Math.atan2(targetY - sourceY, targetX - sourceX);

            let angleDiff = angle - beamAngle;
            angleDiff = Math.atan2(Math.sin(angleDiff), Math.cos(angleDiff));

            const coneHalfAngle = 0.36;
            let lightFactor = 0;

            if (Math.abs(angleDiff) < coneHalfAngle) {
                const edgeDecay = 1 - (Math.abs(angleDiff) / coneHalfAngle);
                const dist = Math.sqrt(dx * dx + dy * dy);
                const distDecay = Math.max(0, 1 - (dist / (height * 1.4)));
                lightFactor = edgeDecay * distDecay * beamIntensity;
            }

            if (lightFactor > 0.05) {
                const currentOpacity = this.maxOpacity * lightFactor * (0.6 + 0.4 * Math.sin(this.flickerTime));
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(230, 203, 106, ${currentOpacity})`;
                ctx.fill();
            }
        }
    }

    // Initialize particles
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    // Classic film grain vertical scratches
    const scratches = [];
    const scratchCount = 2;

    class Scratch {
        constructor() {
            this.reset();
        }

        reset() {
            this.x = Math.random() * width;
            this.width = 0.4 + Math.random() * 0.6;
            this.opacity = 0.02 + Math.random() * 0.06;
            this.life = 0;
            this.maxLife = 8 + Math.random() * 12;
        }

        update() {
            this.life++;
            this.x += (Math.random() - 0.5) * 1.5;
            if (this.life > this.maxLife) {
                this.reset();
            }
        }

        draw() {
            ctx.beginPath();
            ctx.moveTo(this.x, 0);
            ctx.lineTo(this.x + (Math.random() - 0.5) * 4, height);
            ctx.strokeStyle = `rgba(255, 255, 255, ${this.opacity})`;
            ctx.lineWidth = this.width;
            ctx.stroke();
        }
    }

    for (let i = 0; i < scratchCount; i++) {
        scratches.push(new Scratch());
    }

    let flickerVal = 1;
    let time = 0;

    function animate() {
        ctx.clearRect(0, 0, width, height);

        // Smooth cursor follow with lerp
        mouseX += (targetMouseX - mouseX) * 0.08;
        mouseY += (targetMouseY - mouseY) * 0.08;

        time += 0.06;
        flickerVal = 0.88 + Math.sin(time * 6.5) * 0.035 + Math.sin(time * 18.2) * 0.045 + Math.random() * 0.04;

        // Projector light source location
        const sourceX = width / 2 + (mouseX - width / 2) * 0.08;
        const sourceY = -60;

        // Center screen projection focus
        const targetX = width / 2 + (mouseX - width / 2) * 0.45;
        const targetY = height * 0.6 + (mouseY - height / 2) * 0.3;

        // Radial beam gradient mimicking projector lens lamp
        const grad = ctx.createRadialGradient(sourceX, sourceY, 15, targetX, targetY, height * 1.25);
        const beamOpacity = 0.15 * flickerVal;
        
        grad.addColorStop(0, `rgba(230, 203, 106, ${beamOpacity * 1.6})`);
        grad.addColorStop(0.15, `rgba(230, 203, 106, ${beamOpacity * 0.85})`);
        grad.addColorStop(0.45, `rgba(230, 203, 106, ${beamOpacity * 0.35})`);
        grad.addColorStop(0.8, `rgba(230, 203, 106, ${beamOpacity * 0.06})`);
        grad.addColorStop(1, 'rgba(2, 6, 23, 0)');

        ctx.fillStyle = grad;
        ctx.beginPath();
        
        // Define cone bounding vectors
        const angle = Math.atan2(targetY - sourceY, targetX - sourceX);
        const halfCone = 0.38;
        ctx.moveTo(sourceX, sourceY);
        ctx.arc(sourceX, sourceY, height * 1.5, angle - halfCone, angle + halfCone);
        ctx.closePath();
        ctx.fill();

        // Projector central hot spot/glow
        const spotGrad = ctx.createRadialGradient(targetX, targetY, 0, targetX, targetY, width * 0.35);
        spotGrad.addColorStop(0, `rgba(230, 203, 106, ${0.04 * flickerVal})`);
        spotGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = spotGrad;
        ctx.beginPath();
        ctx.arc(targetX, targetY, width * 0.35, 0, Math.PI * 2);
        ctx.fill();

        // Render particles
        particles.forEach(p => {
            p.update();
            p.draw(flickerVal);
        });

        // Dynamic film lines/scratches
        if (Math.random() < 0.88) {
            scratches.forEach(s => {
                s.update();
                s.draw();
            });
        }

        // Projector gate hair artifact blinking for 1 frame
        if (Math.random() < 0.045) {
            ctx.beginPath();
            const hx = Math.random() * width;
            const hy = Math.random() * height;
            ctx.moveTo(hx, hy);
            ctx.bezierCurveTo(hx + 12, hy + 6, hx - 4, hy + 14, hx + 10, hy + 20);
            ctx.strokeStyle = `rgba(255, 255, 255, ${0.12 + Math.random() * 0.2})`;
            ctx.lineWidth = 0.7;
            ctx.stroke();
        }

        requestAnimationFrame(animate);
    }

    animate();
})();
</script>

<?php get_footer(); ?>
