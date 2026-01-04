<nav class="main-nav navbar navbar-expand-lg">
    <div class="container">
        <ul class="navbar-nav me-auto">
            <?php $base_url = "?supplier_id=" . $supplier_id; ?>
            <li class="nav-item">
                <a class="nav-link <?= $page === 'home' ? 'active' : '' ?>" href="<?= $base_url ?>&page=home">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $page === 'products' ? 'active' : '' ?>" href="<?= $base_url ?>&page=products">Shop</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $page === 'about' ? 'active' : '' ?>" href="<?= $base_url ?>&page=about">About Us</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $page === 'collection' ? 'active' : '' ?>" href="<?= $base_url ?>&page=collection">Collection</a>
            </li>
        </ul>

        <div class="search-bar">
            <input type="text" name="search" id="searchBar" placeholder="Search.....">
            <i class="fas fa-search"></i>
        </div>
    </div>
</nav>



   
<script>
    const searchInput = document.getElementById("searchBar");
    const resultContainer = document.getElementById("productResults");

    if (searchInput && resultContainer) {
        
        let supplierId = new URLSearchParams(window.location.search).get('supplier_id') || 10;

        function fetchProduct(query = "") {
            
            fetch("utils/search.php?supplier_id=" + supplierId, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "search=" + encodeURIComponent(query)
            })
            .then(res => {
                if (!res.ok) throw new Error("404 Not Found"); 
                return res.text();
            })
            .then(data => {
                resultContainer.innerHTML = data;
            })
            .catch(err => console.error("Error:", err));
        }

        fetchProduct(); 

        let debounceTimer;
        searchInput.addEventListener("input", () => { 
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchProduct(searchInput.value);
            }, 300);
        });
    }
</script>