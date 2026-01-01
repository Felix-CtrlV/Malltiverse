<section class="page-content product-page">
    <div class="container">
        <h2 class="text-center mb-4"><i>Luxury Watch</i></h2>

        <div class="search-container">
            <form action="" method="GET" class="d-flex" onsubmit="event.preventDefault();">
                <input type="hidden" name="supplier_id" value="<?= $supplier_id ?>">
                <input class="form-control me-2" type="search" name="query" id="searchBar"
                       placeholder="Search products..." aria-label="Search">
                <button class="btn btn-outline-primary" type="button" onclick="fetchProduct(document.getElementById('searchBar').value)">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="featured-section mt-4">
            <div class="row g-4" id="productResults">
                <div class="col-12 text-center">
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const searchInput = document.getElementById("searchBar");
    const resultContainer = document.getElementById("productResults");

    if (searchInput && resultContainer) {
        let supplierId = <?= json_encode($supplier_id) ?>;

       
        function fetchProduct(query = "") {
            
            fetch("../templates/template5/utils/search.php?supplier_id=" + supplierId, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "search=" + encodeURIComponent(query)
            })
            .then(res => res.text())
            .then(data => {
                resultContainer.innerHTML = data;
            })
            .catch(err => {
                console.error("Error fetching products:", err);
                resultContainer.innerHTML = '<p class="text-danger text-center">Error loading products.</p>';
            });
        }

      
        fetchProduct(""); 
        

        let debounceTimer;
        searchInput.addEventListener("keyup", () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchProduct(searchInput.value);
            }, 300);
        });
    }
</script>