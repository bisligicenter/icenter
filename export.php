<?php
require_once 'db.php';

function exportCSV($products) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_export.csv"');
    $output = fopen('php://output', 'w');
    // Output header row
    fputcsv($output, ['Product Code', 'Brand', 'Model', 'Storage', 'Price', 'Stock Quantity']);
    // Output data rows
    foreach ($products as $product) {
        fputcsv($output, [
            $product['product_code'],
            $product['brand'],
            $product['model'],
            $product['storage'],
            $product['price'],
            $product['stock_quantity']
        ]);
    }
    fclose($output);
    exit;
}

function exportExcel($products) {
    // Simple Excel export using CSV format with .xls extension
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="products_export.xls"');
    $output = fopen('php://output', 'w');
    // Output header row
    fputcsv($output, ['Product Code', 'Brand', 'Model', 'Storage', 'Price', 'Stock Quantity'], "\t");
    // Output data rows
    foreach ($products as $product) {
        fputcsv($output, [
            $product['product_code'],
            $product['brand'],
            $product['model'],
            $product['storage'],
            $product['price'],
            $product['stock_quantity']
        ], "\t");
    }
    fclose($output);
    exit;
}

function exportPDF($products) {
    // Use TCPDF library for PDF generation
    require_once('tcpdf_min/tcpdf.php'); // Adjust path if needed

    $pdf = new TCPDF();
    $pdf->AddPage();
    $html = '<h1>Products Export</h1><table border="1" cellpadding="4"><thead><tr>
        <th>Product Code</th><th>Brand</th><th>Model</th><th>Storage</th><th>Price</th><th>Stock Quantity</th>
        </tr></thead><tbody>';
    foreach ($products as $product) {
        $html .= '<tr>
            <td>'.htmlspecialchars($product['product_code']).'</td>
            <td>'.htmlspecialchars($product['brand']).'</td>
            <td>'.htmlspecialchars($product['model']).'</td>
            <td>'.htmlspecialchars($product['storage']).'</td>
            <td>'.htmlspecialchars($product['price']).'</td>
            <td>'.htmlspecialchars($product['stock_quantity']).'</td>
        </tr>';
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('products_export.pdf', 'D');
    exit;
}

$type = $_GET['type'] ?? '';

try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY product_code ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error fetching products: ' . $e->getMessage());
}

switch (strtolower($type)) {
    case 'csv':
        exportCSV($products);
        break;
    case 'pdf':
        exportPDF($products);
        break;
    case 'excel':
        exportExcel($products);
        break;
    default:
        die('Invalid export type specified.');
}
?>
