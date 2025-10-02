<header class="menu-bar">
    <a href="kiosk.php" class="logo-container">
        <img src="images/iCenter.png" alt="Logo" class="logo">
    </a>
    <div class="menu-wrapper">
        <ul>
            <li><a href="kiosk.php#container2">COLLECTIONS</a></li>
            <li><a href="kiosk.php#container3">PRODUCTS</a></li>
            <li><a href="compare.php">COMPARE</a></li>
            <li><a href="reservations.php">RESERVE</a></li>
            <li><a href="#" id="customerCareLink">CUSTOMER CARE</a></li>
        </ul>
    </div>
    <div class="search-container" style="position:relative;">
        <input type="text" id="searchInput" placeholder="Search products...">
        <button id="searchButton"><i class="fas fa-search"></i></button>
        <div id="searchSuggestions" class="search-suggestions"></div>
    </div>
</header>

<style>
.menu-bar {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 15px 25px;
    display: flex;
    align-items: center;
    position: relative;
}

.logo-container {
    position: relative;
    z-index: 10;
    margin-right: 40px;
    transform: translateY(5px);
}

.logo {
    width: 140px;
    height: auto;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    transition: transform 0.3s ease;
}

.logo:hover {
    transform: scale(1.05);
}

.menu-wrapper ul {
    display: flex;
    gap: 40px;
    justify-content: center;
    margin: 0;
    padding: 0;
}

.menu-wrapper ul li {
    list-style: none;
    position: relative;
}

.menu-wrapper ul li a {
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 12px 5px;
    position: relative;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #333;
    font-size: 1.05rem;
}

.menu-wrapper ul li a:hover {
    color: #007bff;
}

.menu-wrapper ul li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 3px;
    bottom: 0;
    left: 0;
    background-color: #007bff;
    transition: width 0.3s ease;
    border-radius: 3px;
}

.menu-wrapper ul li a:hover::after {
    width: 100%;
}

/* Improve search container */
.search-container {
    margin-left: 30px;
}
</style>