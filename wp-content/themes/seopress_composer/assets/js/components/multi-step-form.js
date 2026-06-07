export default function initMultiStepForm() {
  const containers = document.querySelectorAll('.multi-step-form-component');
  if (containers.length === 0) return;

  containers.forEach(container => {
    const form = container.querySelector('.multi-step-form');
    const steps = container.querySelectorAll('.step');
    const formSteps = container.querySelectorAll('.form-step');
    const prevBtn = container.querySelector('.prev-btn');
    const nextBtn = container.querySelector('.next-btn');
    const submitBtn = container.querySelector('.submit-btn');
    const navContainer = container.querySelector('.form-navigation');

    if (!form || !nextBtn) return;

    let currentStep = 1;

    function updateUI() {
      // Update Steps Indicator
      steps.forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        if (stepNum < currentStep) {
          step.classList.add('step-primary', 'step-success');
          step.classList.remove('step-active');
        } else if (stepNum === currentStep) {
          step.classList.add('step-primary', 'step-active');
          step.classList.remove('step-success');
        } else {
          step.classList.remove('step-primary', 'step-success', 'step-active');
        }
      });

      // Show/Hide Form Steps
      formSteps.forEach(fs => {
        if (parseInt(fs.dataset.step) === currentStep) {
          fs.classList.remove('hidden');
          fs.classList.add('animate-fade-in');
        } else {
          fs.classList.add('hidden');
          fs.classList.remove('animate-fade-in');
        }
      });

      // Update Buttons
      if (currentStep === 1) {
        if (prevBtn) prevBtn.style.visibility = 'hidden';
      } else {
        if (prevBtn) prevBtn.style.visibility = 'visible';
      }

      if (currentStep === 3) {
        if (nextBtn) nextBtn.classList.add('hidden');
        if (submitBtn) submitBtn.classList.remove('hidden');
      } else if (currentStep < 3) {
        if (nextBtn) nextBtn.classList.remove('hidden');
        if (submitBtn) submitBtn.classList.add('hidden');
      } else {
        if (navContainer) navContainer.classList.add('hidden');
      }
    }

    function validateStep(step) {
      const currentStepEl = container.querySelector(`.form-step[data-step="${step}"]`);
      if (!currentStepEl) return true;
      
      const inputs = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
      let isValid = true;

      if (step === 1) {
        const checkboxes = currentStepEl.querySelectorAll('input[type="checkbox"]:checked');
        if (checkboxes.length === 0) {
          isValid = false;
          alert('Bitte wählen Sie mindestens eine Kategorie aus.');
        }
      } else {
        inputs.forEach(input => {
          if (!input.checkValidity()) {
            isValid = false;
            input.reportValidity();
          }
        });
      }
      return isValid;
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        if (validateStep(currentStep)) {
          currentStep++;
          updateUI();
        }
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
          currentStep--;
          updateUI();
        }
      });
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (submitBtn) submitBtn.classList.add('loading');

      const formData = new FormData(form);

      try {
        const response = await fetch('/wp-json/seopress/v1/contact', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        if (submitBtn) submitBtn.classList.remove('loading');

        if (response.ok && result.success) {
          currentStep = 4;
          updateUI();
        } else {
          alert(result.message || 'Ein Fehler ist aufgetreten. Bitte prüfen Sie Ihre Eingaben.');
        }
      } catch (error) {
        console.error('Submission error:', error);
        if (submitBtn) submitBtn.classList.remove('loading');
        // Ignore fetch error in dev if offline, just proceed to success for UX testing,
        // BUT wait, this is production codebase. Show real error.
        alert('Es gab ein technisches Problem. Bitte versuchen Sie es später erneut.');
      }
    });

    updateUI();
  });
}
