// Gestion des cartes sur écran d'accueil
if (typeof window.gsap === 'undefined') {
    window.gsap = require('gsap');
}

if (typeof window.imagesLoaded === 'undefined') {
    window.imagesLoaded = require('imagesLoaded');
}
if (typeof window.buttons === 'undefined') {
    window.buttons = {
        prev: document.querySelector(".btn--left"),
        next: document.querySelector(".btn--right"),
    };
}

if (typeof window.cardsContainerEl === 'undefined') {
    window.cardsContainerEl = document.querySelector(".cards__wrapper");
}

if (typeof window.appBgContainerEl === 'undefined') {
    window.appBgContainerEl = document.querySelector(".app__bg");
}

if (typeof window.cardInfosContainerEl === 'undefined') {
    window.cardInfosContainerEl = document.querySelector(".info__wrapper");
}

// Fonction de temporisation pour éviter les appels répétitifs rapides
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

let isAnimating = false;

// Ajout des événements de clic pour les boutons "prev" et "next"
buttons.next.addEventListener("click", () => {
    if (!isAnimating) {
        swapCards("right");
    }
});

buttons.prev.addEventListener("click", () => {
    if (!isAnimating) {
        swapCards("left");
    }
});

// Ajout de l'événement clavier avec la fonction debounce qui permet de limiter les appels répétitifs
document.addEventListener("keydown", debounce((event) => {
    if (!isAnimating) {
        if (event.key === "ArrowLeft" || event.key === "Left") {
            buttons.prev.click();
        } else if (event.key === "ArrowRight" || event.key === "Right") {
            buttons.next.click();
        }
    }
}, 250));

function swapCards(direction) {
    const currentCardEl = cardsContainerEl.querySelector(".current--card");
    const previousCardEl = cardsContainerEl.querySelector(".previous--card");
    const nextCardEl = cardsContainerEl.querySelector(".next--card");

    const currentBgImageEl = appBgContainerEl.querySelector(".current--image");
    const previousBgImageEl = appBgContainerEl.querySelector(".previous--image");
    const nextBgImageEl = appBgContainerEl.querySelector(".next--image");

    changeInfo(direction);
    swapCardsClass();

    removeCardEvents(currentCardEl);

    function swapCardsClass() {
        isAnimating = true; // Empêcher les animations multiples

        currentCardEl.classList.remove("current--card");
        previousCardEl.classList.remove("previous--card");
        nextCardEl.classList.remove("next--card");

        currentBgImageEl.classList.remove("current--image");
        previousBgImageEl.classList.remove("previous--image");
        nextBgImageEl.classList.remove("next--image");

        currentCardEl.style.zIndex = "50";
        currentBgImageEl.style.zIndex = "-2";

        if (direction === "right") {
            previousCardEl.style.zIndex = "20";
            nextCardEl.style.zIndex = "30";

            nextBgImageEl.style.zIndex = "-1";

            currentCardEl.classList.add("previous--card");
            previousCardEl.classList.add("next--card");
            nextCardEl.classList.add("current--card");

            currentBgImageEl.classList.add("previous--image");
            previousBgImageEl.classList.add("next--image");
            nextBgImageEl.classList.add("current--image");
        } else if (direction === "left") {
            previousCardEl.style.zIndex = "30";
            nextCardEl.style.zIndex = "20";

            previousBgImageEl.style.zIndex = "-1";

            currentCardEl.classList.add("next--card");
            previousCardEl.classList.add("current--card");
            nextCardEl.classList.add("previous--card");

            currentBgImageEl.classList.add("next--image");
            previousBgImageEl.classList.add("current--image");
            nextBgImageEl.classList.add("previous--image");
        }
    }
    setTimeout(() => { // Réinitialiser les événements de la carte après l'animation
        isAnimating = false; // Permettre de nouvelles animations
    }, 1000); // Durée de l'animation en millisecondes (correspond à la durée de l'animation CSS)
}

function changeInfo(direction) {
    let currentInfoEl = cardInfosContainerEl.querySelector(".current--info");
    let previousInfoEl = cardInfosContainerEl.querySelector(".previous--info");
    let nextInfoEl = cardInfosContainerEl.querySelector(".next--info");

    gsap.timeline()
        .to([buttons.prev, buttons.next], {
            duration: 0.2,
            opacity: 0.5,
            pointerEvents: "none",
        })
        .to(
            currentInfoEl.querySelectorAll(".text"),
            {
                duration: 0.4,
                stagger: 0.1,
                translateY: "-120px",
                opacity: 0,
            },
            "-="
        )
        .call(() => {
            swapInfosClass(direction);
        })
        .call(() => initCardEvents())
        .fromTo(
            direction === "right"
                ? nextInfoEl.querySelectorAll(".text")
                : previousInfoEl.querySelectorAll(".text"),
            {
                opacity: 0,
                translateY: "40px",
            },
            {
                duration: 0.4,
                stagger: 0.1,
                translateY: "0px",
                opacity: 1,
            }
        )
        .to([buttons.prev, buttons.next], {
            duration: 0.2,
            opacity: 1,
            pointerEvents: "all",
        });

    function swapInfosClass() {
        currentInfoEl.classList.remove("current--info");
        previousInfoEl.classList.remove("previous--info");
        nextInfoEl.classList.remove("next--info");

        if (direction === "right") {
            currentInfoEl.classList.add("previous--info");
            nextInfoEl.classList.add("current--info");
            previousInfoEl.classList.add("next--info");
        } else if (direction === "left") {
            currentInfoEl.classList.add("next--info");
            nextInfoEl.classList.add("previous--info");
            previousInfoEl.classList.add("current--info");
        }
    }
}

function updateCard(e) {
    const card = e.currentTarget;
    const box = card.getBoundingClientRect();
    const centerPosition = {
        x: box.left + box.width / 2,
        y: box.top + box.height / 2,
    };
    let angle = Math.atan2(e.pageX - centerPosition.x, 0) * (35 / Math.PI);
    gsap.set(card, {
        "--current-card-rotation-offset": `${angle}deg`,
    });
    const currentInfoEl = cardInfosContainerEl.querySelector(".current--info");
    gsap.set(currentInfoEl, {
        rotateY: `${angle}deg`,
    });
}

function resetCardTransforms(e) {
    const card = e.currentTarget;
    const currentInfoEl = cardInfosContainerEl.querySelector(".current--info");
    gsap.set(card, {
        "--current-card-rotation-offset": 0,
    });
    gsap.set(currentInfoEl, {
        rotateY: 0,
    });
}

function initCardEvents() {
    const currentCardEl = cardsContainerEl.querySelector(".current--card");
    currentCardEl.addEventListener("pointermove", updateCard);
    currentCardEl.addEventListener("pointerout", (e) => {
        resetCardTransforms(e);
    });
}

initCardEvents();

function removeCardEvents(card) {
    card.removeEventListener("pointermove", updateCard);
}

function init() {

    let tl = gsap.timeline();

    tl.to(cardsContainerEl.children, {
        delay: 0.15,
        duration: 0.5,
        stagger: {
            ease: "power4.inOut",
            from: "right",
            amount: 0.1,
        },
        "--card-translateY-offset": "0%",
    })
        .to(cardInfosContainerEl.querySelector(".current--info").querySelectorAll(".text"), {
            delay: 0.5,
            duration: 0.4,
            stagger: 0.1,
            opacity: 1,
            translateY: 0,
        })
        .to(
            [buttons.prev, buttons.next],
            {
                duration: 0.4,
                opacity: 1,
                pointerEvents: "all",
            },
            "-=0.4"
        );
}

if (typeof window.waitForImages === 'undefined') {
    window.waitForImages = function () {

        const images = [...document.querySelectorAll("img")];
        const totalImages = images.length;
        let loadedImages = 0;
        const loaderEl = document.querySelector(".loader span");

        gsap.set(cardsContainerEl.children, {
            "--card-translateY-offset": "100vh",
        });
        gsap.set(cardInfosContainerEl.querySelector(".current--info").querySelectorAll(".text"), {
            translateY: "40px",
            opacity: 0,
        });
        gsap.set([buttons.prev, buttons.next], {
            pointerEvents: "none",
            opacity: "0",
        });

        images.forEach((image) => {
            imagesLoaded(image, (instance) => {
                if (instance.isComplete) {
                    loadedImages++;
                    let loadProgress = loadedImages / totalImages;

                    gsap.to(loaderEl, {
                        duration: 2, // Durée de l'animation en secondes
                        scaleX: loadProgress,
                        backgroundColor: `hsl(${loadProgress * 120}, 100%, 50%`,
                    });

                    if (totalImages == loadedImages) {
                        gsap.timeline()
                            .to(".loading__wrapper", {
                                duration: 2, // Durée de l'animation en secondes
                                opacity: 0,
                                pointerEvents: "none",
                            })
                            .call(() => init());
                    }
                }
            });
        });
    };
};   

waitForImages();

// Gestion cercles page d'accueil
$(document).ready(function(){
    $(".txt-block").hide();
    $(".dynamic-circle").each(function(){
        $(this).find('.inCircle').click(function(){
            $(this).closest('.dynamic-circle').find('.txt-block').slideToggle("slow");
        });
    });
});
