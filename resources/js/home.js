const homeHeroStories = [
    {
        eyebrow: 'One connected place for care',
        title: 'Care that feels',
        accent: 'connected.',
        description: 'Medora brings patient information, clinical coordination, and hospital operations into one calm, reliable workspace—so every team can stay focused on the person in front of them.',
    },
    {
        eyebrow: 'A clearer day for every care team',
        title: 'Better context.',
        accent: 'Better decisions.',
        description: 'Bring the details that matter into view for clinicians and hospital teams, so coordination stays simple and care keeps moving forward.',
    },
    {
        eyebrow: 'A more considered arrival',
        title: 'Every patient',
        accent: 'deserves a welcome.',
        description: 'From the first hello to the next follow-up, Medora helps hospitals create a more organised, reassuring experience around every person.',
    },
];

const homeHeroSetText = (id, value) => {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
};

export const initialiseHomePage = () => {
    const slides = Array.from(document.querySelectorAll('[data-home-hero-slide]'));
    const dots = Array.from(document.querySelectorAll('[data-home-hero-dot]'));

    if (slides.length !== homeHeroStories.length || dots.length !== homeHeroStories.length) {
        return;
    }

    let activeIndex = 0;
    let intervalId = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const showStory = (index) => {
        activeIndex = (index + homeHeroStories.length) % homeHeroStories.length;
        const story = homeHeroStories[activeIndex];

        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === activeIndex);
        });

        dots.forEach((dot, dotIndex) => {
            const active = dotIndex === activeIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-current', String(active));
        });

        homeHeroSetText('homeHeroEyebrow', story.eyebrow);
        homeHeroSetText('homeHeroTitle', story.title);
        homeHeroSetText('homeHeroAccent', story.accent);
        homeHeroSetText('homeHeroDescription', story.description);
    };

    const startRotation = () => {
        if (reducedMotion || intervalId) {
            return;
        }

        intervalId = window.setInterval(() => {
            showStory(activeIndex + 1);
        }, 6500);
    };

    const stopRotation = () => {
        if (intervalId) {
            window.clearInterval(intervalId);
            intervalId = null;
        }
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showStory(index);
            stopRotation();
            startRotation();
        });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopRotation();
            return;
        }

        startRotation();
    });

    showStory(0);
    startRotation();
};
