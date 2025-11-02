document.addEventListener('DOMContentLoaded', () => {
    // Look for the container. If it doesn't exist, don't run the rest.
    const sliderContainer = document.getElementById('qa-slider-container');
    if (!sliderContainer) return;

    // Get all important elements
    const slides = document.querySelectorAll('.qa-slide');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const counter = document.getElementById('progress-counter');
    const progressBar = document.getElementById('progress-bar');
    const backLink = document.getElementById('back-to-chapters-link');
    
    const totalQuestions = slides.length;
    let currentIndex = 0;

    // If there are no questions, stop
    if (totalQuestions === 0) return;

    // Function to update the UI (buttons, counter, progress bar)
    function updateUI() {
        // 1. Show the correct slide
        slides.forEach((slide, index) => {
            slide.style.display = index === currentIndex ? 'block' : 'none';
        });

        // --- NEW CODE START ---
        // Scroll to the top of the question content
        const contentTop = document.getElementById('question-content-top');
        if (contentTop) {
            contentTop.scrollIntoView({ behavior: 'smooth' });
        }
        // --- NEW CODE END ---

        // 2. Update the counter
        counter.textContent = `Question ${currentIndex + 1} / ${totalQuestions}`;

        // 3. Update the progress bar
        let percent = (currentIndex + 1) / totalQuestions * 100;
        progressBar.style.width = `${percent}%`;

        // 4. Show/hide Previous button
        btnPrev.style.display = currentIndex === 0 ? 'none' : 'inline-flex';

        // 5. Change Next button text on the last question
        if (currentIndex === totalQuestions - 1) {
            btnNext.innerHTML = 'Done <i class="fas fa-check ml-2"></i>';
        } else {
            btnNext.innerHTML = 'Next <i class="fas fa-arrow-right ml-2"></i>';
        }

        // --- UPDATED CODE BLOCK ---
        // Reset ALL "Show Answer" buttons and hide ALL answers for the new slide
        const currentSlide = slides[currentIndex];
        const allAnswerButtons = currentSlide.querySelectorAll('.btn-show-answer');
        const allAnswerBlocks = currentSlide.querySelectorAll('.answer-part');
        
        allAnswerButtons.forEach(btn => {
            btn.style.display = 'block';
        });
        allAnswerBlocks.forEach(block => {
            block.style.display = 'none';
        });
        // --- END UPDATED CODE BLOCK ---
    }

    // --- Event Listeners ---

    // Next button click
    btnNext.addEventListener('click', () => {
        if (currentIndex < totalQuestions - 1) {
            // Go to the next question
            currentIndex++;
            updateUI();
        } else {
            // This was the last question, go back to the chapter list
            window.location.href = backLink.href;
        }
    });

    // Previous button click
    btnPrev.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateUI();
        }
    });

    // "Show Answer" buttons
    document.querySelectorAll('.btn-show-answer').forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Find the next element, which is the answer block
            const answerBlock = e.target.nextElementSibling;
            if (answerBlock && answerBlock.classList.contains('answer-part')) {
                answerBlock.style.display = 'block';
                e.target.style.display = 'none'; // Hide the "Show Answer" button
            }
        });
    });

    // Initialize the UI on page load
    updateUI();
});