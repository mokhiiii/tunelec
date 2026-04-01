// Array of questions with their IDs and text
const questions = [
    { id: 'q1_1', text: 'Question 1.1: Vérification des connexions électriques' },
    { id: 'q1_2', text: 'Question 1.2: État des disjoncteurs' },
    { id: 'q2_1', text: 'Question 2.1: Propreté du tableau électrique' },
    { id: 'q2_2', text: 'Question 2.2: Signalisation de sécurité' }
    // Add more questions as needed
];

// Store audit information
let auditInfo = {
    atelier: '',
    zone: '',
    date: new Date().toISOString().split('T')[0],
    auditeur: '',
    commentaires: '',
    answers: {}
};

// Default to deployed backend; override via window.TUNELEC_API_BASE when needed.
const API_BASE = window.TUNELEC_API_BASE || 'https://tunelec.onrender.com';
function apiUrl(path) {
    if (!API_BASE) return path;
    const base = API_BASE.replace(/\/$/, '');
    const cleanPath = path.replace(/^\//, '');
    return `${base}/${cleanPath}`;
}

// Function to load image for a question
async function loadQuestionImage(questionId) {
    try {
        const response = await fetch(apiUrl(`image_handler.php?question_id=${questionId}`));
        const data = await response.json();
        
        if (data.success) {
            return `data:${data.type};base64,${data.image}`;
        }
        return null;
    } catch (error) {
        console.error('Error loading image:', error);
        return null;
    }
}

// Function to create the question form
async function createQuestionForm() {
    const container = document.getElementById('questionForm');
    container.innerHTML = ''; // Clear existing content
    
    for (const question of questions) {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-container';
        
        // Question text
        const textDiv = document.createElement('div');
        textDiv.className = 'question-text';
        textDiv.textContent = question.text;
        questionDiv.appendChild(textDiv);
        
        // Image container
        const imageDiv = document.createElement('div');
        imageDiv.className = 'question-image';
        
        // Load and display the image
        const imageUrl = await loadQuestionImage(question.id);
        if (imageUrl) {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = `Image for ${question.text}`;
            img.onclick = () => {
                // Create fullscreen preview
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.onclick = () => preview.remove();
                
                const previewImg = document.createElement('img');
                previewImg.src = imageUrl;
                preview.appendChild(previewImg);
                document.body.appendChild(preview);
            };
            imageDiv.appendChild(img);
        }
        questionDiv.appendChild(imageDiv);
        
        // Answer options (Ok/Nok/NA)
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'answer-options';
        
        ['Ok', 'Nok', 'N/A'].forEach(option => {
            const label = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = question.id;
            radio.value = option;
            radio.onchange = () => {
                auditInfo.answers[question.id] = {
                    value: option,
                    comment: commentInput.value
                };
            };
            label.appendChild(radio);
            label.appendChild(document.createTextNode(option));
            optionsDiv.appendChild(label);
        });
        questionDiv.appendChild(optionsDiv);
        
        // Comment input
        const commentInput = document.createElement('textarea');
        commentInput.placeholder = 'Commentaire';
        commentInput.onchange = () => {
            if (!auditInfo.answers[question.id]) {
                auditInfo.answers[question.id] = { value: null };
            }
            auditInfo.answers[question.id].comment = commentInput.value;
        };
        questionDiv.appendChild(commentInput);
        
        container.appendChild(questionDiv);
    }
}

// Initialize the form when the page loads
document.addEventListener('DOMContentLoaded', () => {
    createQuestionForm();
    
    // Update auditInfo when inputs change
    document.getElementById('atelier').onchange = function() {
        auditInfo.atelier = this.value;
    };
    
    document.getElementById('zone').onchange = function() {
        auditInfo.zone = this.value;
    };
    
    document.getElementById('auditeur').onchange = function() {
        auditInfo.auditeur = this.value;
    };
});
