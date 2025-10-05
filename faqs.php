<?php
require_once 'config/database.php';
$db = Database::getInstance();

$faqs = $db->fetchAll(
    "SELECT * FROM faqs WHERE status = 'active' ORDER BY category, order_num, id"
);

// Group FAQs by category
$groupedFaqs = [];
foreach ($faqs as $faq) {
    $category = $faq['category'] ?: 'General';
    if (!isset($groupedFaqs[$category])) {
        $groupedFaqs[$category] = [];
    }
    $groupedFaqs[$category][] = $faq;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our tours and services</p>
        </div>
    </div>

    <section class="faqs-section">
        <div class="container">
            <div class="faqs-layout">
                <!-- Categories Sidebar -->
                <aside class="faqs-sidebar">
                    <div class="faqs-search">
                        <input type="text" id="faqSearch" placeholder="Search FAQs..." class="form-control">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <h3>Categories</h3>
                    <ul class="faqs-categories">
                        <li><a href="#all" class="active" onclick="filterCategory('all')">All Questions</a></li>
                        <?php foreach ($groupedFaqs as $category => $items): ?>
                            <li><a href="#<?= strtolower(str_replace(' ', '-', $category)) ?>" 
                                   onclick="filterCategory('<?= $category ?>')">
                                <?= $category ?> (<?= count($items) ?>)
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="contact-box">
                        <i class="fas fa-headset"></i>
                        <h4>Still have questions?</h4>
                        <p>Can't find what you're looking for?</p>
                        <a href="contact.php" class="btn btn-primary btn-sm">Contact Us</a>
                    </div>
                </aside>
                
                <!-- FAQs Content -->
                <div class="faqs-content">
                    <?php if (empty($faqs)): ?>
                        <div class="no-faqs">
                            <i class="fas fa-question-circle"></i>
                            <h3>No FAQs Available</h3>
                            <p>Check back soon for frequently asked questions.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groupedFaqs as $category => $items): ?>
                            <div class="faq-category-section" data-category="<?= $category ?>">
                                <h2 class="category-title">
                                    <i class="fas fa-folder"></i> <?= $category ?>
                                </h2>
                                
                                <div class="faq-list">
                                    <?php foreach ($items as $index => $faq): ?>
                                        <div class="faq-item" data-question="<?= strtolower($faq['question']) ?>" 
                                             data-answer="<?= strtolower($faq['answer']) ?>">
                                            <div class="faq-question" onclick="toggleFaq(this)">
                                                <h3><?= $faq['question'] ?></h3>
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                            <div class="faq-answer">
                                                <p><?= nl2br($faq['answer']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div id="noResults" class="no-results" style="display: none;">
                            <i class="fas fa-search"></i>
                            <h3>No Results Found</h3>
                            <p>Try different keywords or browse by category</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Topics -->
    <section class="section" style="background: var(--light);">
        <div class="container">
            <h2 class="section-title">Popular Topics</h2>
            <div class="topics-grid">
                <a href="#" class="topic-card" onclick="searchFaq('gorilla trekking'); return false;">
                    <i class="fas fa-paw"></i>
                    <h4>Gorilla Trekking</h4>
                    <p>Permits, booking, what to expect</p>
                </a>
                
                <a href="#" class="topic-card" onclick="searchFaq('visa'); return false;">
                    <i class="fas fa-passport"></i>
                    <h4>Visa & Entry</h4>
                    <p>Requirements and procedures</p>
                </a>
                
                <a href="#" class="topic-card" onclick="searchFaq('payment'); return false;">
                    <i class="fas fa-credit-card"></i>
                    <h4>Payment</h4>
                    <p>Methods and currency</p>
                </a>
                
                <a href="#" class="topic-card" onclick="searchFaq('cancellation'); return false;">
                    <i class="fas fa-ban"></i>
                    <h4>Cancellation</h4>
                    <p>Policy and refunds</p>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const icon = element.querySelector('i');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem && item.classList.contains('active')) {
                    item.classList.remove('active');
                    item.querySelector('.faq-answer').style.maxHeight = null;
                    item.querySelector('.faq-question i').style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle current FAQ
            faqItem.classList.toggle('active');
            
            if (faqItem.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
            } else {
                answer.style.maxHeight = null;
                icon.style.transform = 'rotate(0deg)';
            }
        }
        
        function filterCategory(category) {
            const sections = document.querySelectorAll('.faq-category-section');
            const links = document.querySelectorAll('.faqs-categories a');
            
            // Update active link
            links.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter sections
            sections.forEach(section => {
                if (category === 'all' || section.dataset.category === category) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        }
        
        function searchFaq(query) {
            const searchInput = document.getElementById('faqSearch');
            searchInput.value = query;
            performSearch();
        }
        
        // Search functionality
        document.getElementById('faqSearch').addEventListener('input', performSearch);
        
        function performSearch() {
            const query = document.getElementById('faqSearch').value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            const sections = document.querySelectorAll('.faq-category-section');
            let hasResults = false;
            
            if (query === '') {
                // Show all
                faqItems.forEach(item => item.style.display = 'block');
                sections.forEach(section => section.style.display = 'block');
                document.getElementById('noResults').style.display = 'none';
                return;
            }
            
            sections.forEach(section => {
                let sectionHasResults = false;
                const items = section.querySelectorAll('.faq-item');
                
                items.forEach(item => {
                    const question = item.dataset.question;
                    const answer = item.dataset.answer;
                    
                    if (question.includes(query) || answer.includes(query)) {
                        item.style.display = 'block';
                        sectionHasResults = true;
                        hasResults = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                section.style.display = sectionHasResults ? 'block' : 'none';
            });
            
            document.getElementById('noResults').style.display = hasResults ? 'none' : 'block';
        }
    </script>
    
    <style>
        .faqs-section {
            padding: 4rem 0;
        }
        
        .faqs-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 3rem;
        }
        
        .faqs-sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .faqs-search {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .faqs-search input {
            padding-right: 3rem;
        }
        
        .faqs-search i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .faqs-sidebar h3 {
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .faqs-categories {
            list-style: none;
            margin-bottom: 2rem;
        }
        
        .faqs-categories li {
            margin-bottom: 0.5rem;
        }
        
        .faqs-categories a {
            display: block;
            padding: 0.8rem 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .faqs-categories a:hover,
        .faqs-categories a.active {
            background: var(--primary);
            color: white;
        }
        
        .contact-box {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
        }
        
        .contact-box i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .contact-box h4 {
            margin-bottom: 0.5rem;
        }
        
        .contact-box p {
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .category-title {
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .faq-category-section {
            margin-bottom: 3rem;
        }
        
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .faq-item:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            user-select: none;
        }
        
        .faq-question h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .faq-question i {
            color: var(--primary);
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .faq-answer p {
            padding: 0 1.5rem 1.5rem;
            color: var(--gray);
            line-height: 1.8;
        }
        
        .topics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        
        .topic-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .topic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .topic-card i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .topic-card h4 {
            margin-bottom: 0.5rem;
        }
        
        .topic-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .no-results, .no-faqs {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }
        
        .no-results i, .no-faqs i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .faqs-layout {
                grid-template-columns: 1fr;
            }
            
            .faqs-sidebar {
                position: static;
            }
            
            .topics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
