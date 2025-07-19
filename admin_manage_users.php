<?php
// admin_manage_users.php
include 'config.php';
include 'header.php';

// Pastikan admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Pagination (opsional, tapi bagus untuk banyak user)
$limit = 20; // Jumlah user per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil daftar pengguna beserta saldo mereka
$stmtUsers = $pdo->prepare("SELECT id, username, role, balance, created_at FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmtUsers->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmtUsers->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtUsers->execute();
$users_list = $stmtUsers->fetchAll();

// Hitung total pengguna untuk pagination
$totalUsersStmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = (int)$totalUsersStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

?>

<div class="container mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Manage Users</h1>
        <p class="text-sm text-gray-500">View and manage user accounts and their balances.</p>
    </header>

    <section class="bg-white shadow-lg rounded-lg overflow-hidden">
        <header class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-700">User List (<?= $totalUsers ?> total)</h2>
            </header>

        <div class="p-6">
            <?php if (empty($users_list)): ?>
                <div class="text-center py-10">
                    <p class="text-gray-500">No users found.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance (Rp)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users_list as $user_item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $user_item['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($user_item['username']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $user_item['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= htmlspecialchars(ucfirst($user_item['role'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"><?= number_format($user_item['balance'], 2, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(date("d M Y, H:i", strtotime($user_item['created_at']))) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="admin_edit_user.php?user_id=<?= $user_item['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit User (Not Implemented)">
                                            <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                                        </a>
                                        <a href="admin_user_transactions.php?user_id=<?= $user_item['id'] ?>" class="text-green-600 hover:text-green-900" title="View Transactions (Not Implemented)">
                                             <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                                        </a>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4 rounded-b-lg">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Previous </a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Next </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium"><?= min($offset + 1, $totalUsers) ?></span>
                            to
                            <span class="font-medium"><?= min($offset + $limit, $totalUsers) ?></span>
                            of
                            <span class="font-medium"><?= $totalUsers ?></span>
                            results
                        </p>
                        </div>
                        <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                            <?php endif; ?>
                            
                            <?php 
                            // Logika untuk menampilkan beberapa nomor halaman
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            if ($startPage > 1) echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                            <a href="?page=<?= $i ?>" aria-current="<?= $page == $i ? 'page' : 'false' ?>" class="relative inline-flex items-center px-4 py-2 border <?= $page == $i ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> text-sm font-medium"> <?= $i ?> </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages) echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>'; ?>

                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                            <?php endif; ?>
                        </nav>
                        </div>
                    </div>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>