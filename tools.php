<?php
include 'config.php';
include 'header.php';
include 'includes/sidebar.php';

// Get all tools
$tools = $conn->query("
    SELECT t.*, tc.name AS category_name
    FROM tools t
    JOIN tool_categories tc ON t.category_id = tc.id
    ORDER BY tc.name, t.name
");

$grouped_tools = [];
while($tool = $tools->fetch_assoc()) {
    $grouped_tools[$tool['category_name']][] = $tool;
}
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <h1 class="text-3xl font-bold text-red-600 mb-4 md:mb-0">Tuning Tools</h1>
        
        <!-- Category Filter -->
        <div class="relative w-full md:w-64">
            <select id="categoryFilter" 
                    class="w-full px-4 py-2 rounded-lg border border-red-300 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white appearance-none">
                <option value="all">All Brands</option>
                <?php 
                $categories = $conn->query("
                    SELECT DISTINCT tc.id, tc.name 
                    FROM tool_categories tc
                    JOIN tools t ON tc.id = t.category_id
                    ORDER BY tc.name
                ");
                while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($cat['name']) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-red-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tools Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($grouped_tools as $category => $tools): ?>
        <div class="category-group" data-category="<?= htmlspecialchars($category) ?>">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-red-200 pb-2">
                <?= htmlspecialchars($category) ?>
            </h2>
            <div class="space-y-4">
                <?php foreach($tools as $tool): ?>
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <?= htmlspecialchars($tool['name']) ?>
                        </h3>
                        <span class="px-2 py-1 text-sm rounded-full bg-red-100 text-red-800">
                            <?= $tool['file_path'] ? strtoupper(pathinfo($tool['file_path'], PATHINFO_EXTENSION)) : 'URL' ?>
                        </span>
                    </div>
                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($tool['description']) ?></p>
                    <div class="mt-4">
                        <?php if($tool['file_path']): ?>
                            <a href="tools/download.php?id=<?= $tool['id'] ?>" 
                               class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        <?php elseif($tool['download_url']): ?>
                            <a href="<?= htmlspecialchars($tool['download_url']) ?>" target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Visit Link
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Category Filter
document.getElementById('categoryFilter').addEventListener('change', function(e) {
    const selectedCategory = e.target.value.toLowerCase();
    
    document.querySelectorAll('.category-group').forEach(group => {
        const groupCategory = group.dataset.category.toLowerCase();
        group.style.display = (selectedCategory === 'all' || groupCategory === selectedCategory) 
            ? 'block' 
            : 'none';
    });
});
</script>

<?php include 'footer.php'; ?>