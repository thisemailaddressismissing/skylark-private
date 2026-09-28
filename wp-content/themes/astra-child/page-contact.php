<?php
/**
 * Template Name: Custom Contact Page
 *
 * @package Astra Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div id="primary" class="content-area primary" style="width: 100%; margin: 0 auto;">
    <main id="main" class="site-main">
        <div class="skylark-page-contact-wrapper">
            <!-- SECTION 1: MAIN INTERACTIVE FORM -->
            <div class="custom-contact-container">
                <div class="contact-header">
                    <h1>Contact Us</h1>
                    <a href="mailto:contact@skylarkapparelltd.com" class="contact-email-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                        harun_skylark@hotmail.com
                    </a>
                </div>

                <form id="skylark-contact-form" method="POST">
                    <?php wp_nonce_field( 'custom_contact_nonce', 'nonce' ); ?>
                    <input type="hidden" name="action" value="submit_contact_form">
                    <input type="hidden" name="role" id="selected-role" value="Buyer">

                    <!-- Question: What describes you best? (Sliding Glider Tabs) -->
                    <div class="contact-role-section">
                        <div class="contact-role-label">What describes you best?</div>
                        <div class="role-glider-tabs-container">
                            <div class="role-glider-tabs">
                                <input type="radio" id="role-buyer" name="role_tab" value="Buyer" data-desc="Looking to source from us" checked />
                                <label class="role-tab" for="role-buyer">Buyer</label>

                                <input type="radio" id="role-supplier" name="role_tab" value="Supplier" data-desc="Interested in providing materials or equipment" />
                                <label class="role-tab" for="role-supplier">Supplier</label>

                                <input type="radio" id="role-partner" name="role_tab" value="Partner" data-desc="Looking to explore long-term business partnerships" />
                                <label class="role-tab" for="role-partner">Partner</label>

                                <input type="radio" id="role-other" name="role_tab" value="Someone else" data-desc="General inquiries or questions" />
                                <label class="role-tab" for="role-other">Someone else</label>

                                <span class="glider"></span>
                            </div>
                        </div>
                        <div class="role-subtext" id="role-description">Looking to source from us</div>
                    </div>

                    <!-- Form fields -->
                    <div class="contact-form-grid">
                        <div class="contact-form-group">
                            <label for="full_name">Full Name <span class="required-star">*</span></label>
                            <input type="text" id="full_name" name="full_name" placeholder="Enter Your Name" required>
                        </div>

                        <div class="contact-form-group">
                            <label for="email">Email Address <span class="required-star">*</span></label>
                            <input type="email" id="email" name="email" placeholder="Enter Your Email" required>
                        </div>

                        <div class="contact-form-group full-width">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>

                        <div class="contact-form-group full-width">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Tell us about your requirements..."></textarea>
                        </div>

                        <div class="contact-form-actions">
                            <button type="submit" class="button" id="submit-btn">
                                <span class="default">Send Message</span>
                                <span class="success">Sent</span>
                                <div class="left"></div>
                                <div class="right"></div>
                            </button>
                            <div id="contact-feedback" class="contact-feedback-msg"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECTION 2 & 3: DIRECT CONTACT INFORMATION & LOCATION -->
            <div class="contact-additional-sections">
                <div class="contact-info-card">
                    <h3>Direct Contact</h3>
                    <p><strong>Phone:</strong><br>+8801911345129<br>+8801760777666</p>
                </div>
                <div class="contact-info-card">
                    <h3>Head Office & Factory</h3>
                    <p>Choto Kaliakoir, Birulia, Savar,<br>Dhaka-1430, Bangladesh</p>
                </div>
                <div class="contact-info-card">
                    <h3>Working Hours</h3>
                    <p>Saturday – Thursday<br>9:00 AM – 6:00 PM (GMT+6)</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleRadios = document.querySelectorAll('input[name="role_tab"]');
    const roleInput = document.getElementById('selected-role');
    const roleDesc = document.getElementById('role-description');
    const glider = document.querySelector('.role-glider-tabs .glider');
    const form = document.getElementById('skylark-contact-form');
    const submitBtn = document.getElementById('submit-btn');
    const feedback = document.getElementById('contact-feedback');
    const phoneInput = document.querySelector("#phone");

    // Dynamic Glider Sizing & Positioning to completely cover every word
    function updateGlider() {
        const checked = document.querySelector('input[name="role_tab"]:checked');
        if (!checked || !glider) return;
        const label = document.querySelector(`label[for="${checked.id}"]`);
        if (!label) return;

        glider.style.width = label.offsetWidth + 'px';
        glider.style.height = label.offsetHeight + 'px';
        glider.style.top = label.offsetTop + 'px';
        glider.style.transform = `translateX(${label.offsetLeft}px)`;
    }

    // Role Glider Tab selection
    roleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                roleInput.value = this.value;
                roleDesc.textContent = this.getAttribute('data-desc');
                updateGlider();
            }
        });
    });

    // Initialize glider on load & window resize / device orientation change
    setTimeout(updateGlider, 50);
    window.addEventListener('resize', updateGlider);
    window.addEventListener('orientationchange', () => setTimeout(updateGlider, 100));

    // Initialize International Telephone Input with Country Code Selector
    let iti = null;
    if (typeof window.intlTelInput !== 'undefined' && phoneInput) {
        iti = window.intlTelInput(phoneInput, {
            initialCountry: "bd",
            preferredCountries: ["bd", "us", "gb", "ca", "ae", "sa", "in", "de"],
            separateDialCode: true,
            autoPlaceholder: "aggressive",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js"
        });
    }

    // GSAP Paper Plane Animation
    function runPlaneAnimation(button, callback) {
        if (typeof gsap === 'undefined') {
            if (callback) callback();
            return;
        }

        let getVar = variable => getComputedStyle(button).getPropertyValue(variable);

        button.classList.add('active');

        gsap.to(button, {
            keyframes: [{
                '--left-wing-first-x': 50,
                '--left-wing-first-y': 100,
                '--right-wing-second-x': 50,
                '--right-wing-second-y': 100,
                duration: .2,
                onComplete() {
                    gsap.set(button, {
                        '--left-wing-first-y': 0,
                        '--left-wing-second-x': 40,
                        '--left-wing-second-y': 100,
                        '--left-wing-third-x': 0,
                        '--left-wing-third-y': 100,
                        '--left-body-third-x': 40,
                        '--right-wing-first-x': 50,
                        '--right-wing-first-y': 0,
                        '--right-wing-second-x': 60,
                        '--right-wing-second-y': 100,
                        '--right-wing-third-x': 100,
                        '--right-wing-third-y': 100,
                        '--right-body-third-x': 60
                    });
                }
            }, {
                '--left-wing-third-x': 20,
                '--left-wing-third-y': 90,
                '--left-wing-second-y': 90,
                '--left-body-third-y': 90,
                '--right-wing-third-x': 80,
                '--right-wing-third-y': 90,
                '--right-body-third-y': 90,
                '--right-wing-second-y': 90,
                duration: .2
            }, {
                '--rotate': 50,
                '--left-wing-third-y': 95,
                '--left-wing-third-x': 27,
                '--right-body-third-x': 45,
                '--right-wing-second-x': 45,
                '--right-wing-third-x': 60,
                '--right-wing-third-y': 83,
                duration: .25
            }, {
                '--rotate': 55,
                '--plane-x': -8,
                '--plane-y': 24,
                duration: .2
            }, {
                '--rotate': 40,
                '--plane-x': 45,
                '--plane-y': -180,
                '--plane-opacity': 0,
                duration: .3,
                onComplete() {
                    setTimeout(() => {
                        button.removeAttribute('style');
                        gsap.fromTo(button, {
                            opacity: 0,
                            y: -8
                        }, {
                            opacity: 1,
                            y: 0,
                            clearProps: true,
                            duration: .3,
                            onComplete() {
                                button.classList.remove('active');
                                if (callback) callback();
                            }
                        });
                    }, 2200);
                }
            }]
        });

        gsap.to(button, {
            keyframes: [{
                '--text-opacity': 0,
                '--border-radius': 0,
                '--left-wing-background': getVar('--primary-darkest'),
                '--right-wing-background': getVar('--primary-darkest'),
                duration: .1
            }, {
                '--left-wing-background': getVar('--primary'),
                '--right-wing-background': getVar('--primary'),
                duration: .1
            }, {
                '--left-body-background': getVar('--primary-dark'),
                '--right-body-background': getVar('--primary-darkest'),
                duration: .4
            }, {
                '--success-opacity': 1,
                '--success-scale': 1,
                duration: .25,
                delay: .25
            }]
        });
    }

    // Form Submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (submitBtn.classList.contains('active')) {
            return; // Already submitting
        }

        feedback.className = 'contact-feedback-msg';
        feedback.style.display = 'none';

        const formData = new FormData(form);

        // Include complete international phone number with selected country dial code
        if (iti) {
            const fullPhone = iti.getNumber();
            if (fullPhone) {
                formData.set('phone', fullPhone);
            }
        }

        // Trigger the paper plane animation!
        runPlaneAnimation(submitBtn);

        // Submit via AJAX
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    feedback.className = 'contact-feedback-msg success';
                    feedback.textContent = data.data.message;
                    form.reset();
                    if (iti) {
                        iti.setCountry("bd");
                    }
                    const defaultRadio = document.getElementById('role-buyer');
                    if (defaultRadio) {
                        defaultRadio.checked = true;
                        roleInput.value = defaultRadio.value;
                        roleDesc.textContent = defaultRadio.getAttribute('data-desc');
                        updateGlider();
                    }
                }, 1000);
            } else {
                feedback.className = 'contact-feedback-msg error';
                feedback.textContent = data.data.message || 'An error occurred. Please try again.';
            }
        })
        .catch(err => {
            feedback.className = 'contact-feedback-msg error';
            feedback.textContent = 'Network error. Please try again.';
        });
    });
});
</script>

<?php get_footer(); ?>
