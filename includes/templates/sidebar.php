<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- دکمه منو موبایل -->
<button id="menuBtn" class="lg:hidden fixed top-4 right-4 z-50 p-3 bg-slate-900 text-white rounded-xl shadow-lg">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<!-- اورلی موبایل -->
<div id="overlay" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>

<aside id="sidebar" class="fixed right-0 top-0 h-full w-80 bg-gradient-to-b from-slate-900 to-slate-800 text-white flex flex-col shadow-2xl z-50 transform translate-x-full lg:translate-x-0 transition-transform duration-300">
    <!-- لوگو -->
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-7 h-7 text-slate-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg">پست دفتر</h1>
                <p class="text-xs text-slate-400">سیستم مدیریت بسته‌ها</p>
            </div>
        </div>
    </div>
    
    <!-- منو -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">منوی اصلی</p>
        
        <a href="/admin/dashboard.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'dashboard' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
            </div>
            <span class="font-medium">داشبورد</span>
        </a>
        
        <a href="/admin/register.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'register' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="font-medium">ثبت بسته</span>
        </a>
        
        <a href="/admin/deliver.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'deliver' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <span class="font-medium">تحویل بسته</span>
        </a>
        
        <a href="/admin/delivered.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'delivered' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="font-medium">تحویل شده‌ها</span>
        </a>
        
        <p class="px-3 py-2 mt-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">ابزارها</p>
        
        <a href="/admin/search.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'search' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
            <span class="font-medium">جستجو</span>
        </a>
        
        <a href="/admin/types.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?= $currentPage === 'types' ? 'bg-gradient-to-l from-amber-500/20 to-transparent text-amber-400 border-r-[3px] border-amber-400' : 'hover:bg-white/5 text-slate-300 hover:text-white' ?>">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                </svg>
            </div>
            <span class="font-medium">انواع بسته</span>
        </a>
    </nav>
    
    <!-- کاربر -->
    <div class="p-4 border-t border-white/10 bg-white/5">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-lg">
                <?= mb_substr(Auth::getFullName(), 0, 1) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-white truncate"><?= Security::escape(Auth::getFullName()) ?></p>
                <p class="text-xs text-slate-400">مدیر سیستم</p>
            </div>
            <a href="/logout.php" class="p-2.5 hover:bg-red-500/20 rounded-xl text-slate-400 hover:text-red-400 transition-colors" title="خروج">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>
            </a>
        </div>
    </div>
</aside>

<script>
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

menuBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('translate-x-full');
    overlay.classList.toggle('hidden');
});

overlay?.addEventListener('click', () => {
    sidebar.classList.add('translate-x-full');
    overlay.classList.add('hidden');
});
</script>
