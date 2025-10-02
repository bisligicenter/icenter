<?php
session_start();
require_once 'db.php';
require_once 'tcpdf/tcpdf.php';

try {
    $pdo = getConnection();
    
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    // Query to get stock in records from stock_in table with enhanced data
    $query = "
        SELECT 
            si.id as movement_id,
            DATE_FORMAT(si.created_at, '%Y-%m-%d %H:%i:%s') as date_time,
            DATE(si.created_at) as date,
            p.product_id,
            p.product as product_name,
            p.brand,
            p.model,
            p.storage,
            si.quantity as quantity_added,
            si.previous_stock,
            si.new_stock,
            si.created_by as added_by,
            COALESCE(si.notes, 'No notes provided') as notes,
            p.stock_quantity as current_stock
        FROM stock_in si
        JOIN products p ON si.product_id = p.product_id
        WHERE DATE(si.created_at) BETWEEN :start_date AND :end_date
        ORDER BY si.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if stock_in table exists
    $table_check_query = "SHOW TABLES LIKE 'stock_in'";
    $table_check_stmt = $pdo->prepare($table_check_query);
    $table_check_stmt->execute();
    
    if ($table_check_stmt->rowCount() == 0) {
        // Table doesn't exist, create a PDF with error message
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('iCenter Inventory System');
        $pdf->SetAuthor('iCenter');
        $pdf->SetTitle('Stock In Report - Setup Required');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Stock In Report', 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Stock in tracking is not set up.', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Please run the database setup first.', 0, 1, 'C');
        
        $pdf->Output('stock_in_report.pdf', 'D');
        exit;
    }
    
    // Create PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('iCenter Inventory System');
    $pdf->SetAuthor('iCenter');
    $pdf->SetTitle('Stock In Report');
    $pdf->SetSubject('Stock In Report from ' . $start_date . ' to ' . $end_date);
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, 'Stock In Report - Movement History', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Date range
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Period: ' . date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Summary statistics
    $total_movements = count($results);
    $total_quantity_added = array_sum(array_column($results, 'quantity_added'));
    $unique_products = count(array_unique(array_column($results, 'product_id')));
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'Summary:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, 'Total Movements: ' . $total_movements, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Total Quantity Added: ' . $total_quantity_added, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Unique Products: ' . $unique_products, 0, 1, 'L');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(240, 240, 240);
    
    // Define column widths for 9 columns
    $w = array(25, 15, 35, 20, 25, 20, 20, 20, 25);
    
    // Header row
    $pdf->Cell($w[0], 7, 'Date & Time', 1, 0, 'C', true);
    $pdf->Cell($w[1], 7, 'ID', 1, 0, 'C', true);
    $pdf->Cell($w[2], 7, 'Product', 1, 0, 'C', true);
    $pdf->Cell($w[3], 7, 'Brand', 1, 0, 'C', true);
    $pdf->Cell($w[4], 7, 'Model', 1, 0, 'C', true);
    $pdf->Cell($w[5], 7, 'Storage', 1, 0, 'C', true);
    $pdf->Cell($w[6], 7, 'Qty Added', 1, 0, 'C', true);
    $pdf->Cell($w[7], 7, 'Prev Stock', 1, 0, 'C', true);
    $pdf->Cell($w[8], 7, 'New Stock', 1, 1, 'C', true);
    
    // Data rows
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetFillColor(255, 255, 255);
    
    if (empty($results)) {
        $pdf->Cell(array_sum($w), 10, 'No stock in records found for the selected date range', 1, 1, 'C');
    } else {
        foreach ($results as $row) {
            $pdf->Cell($w[0], 6, $row['date_time'], 1, 0, 'C');
            $pdf->Cell($w[1], 6, $row['product_id'], 1, 0, 'C');
            $pdf->Cell($w[2], 6, substr($row['product_name'], 0, 20), 1, 0, 'L');
            $pdf->Cell($w[3], 6, substr($row['brand'] ?? 'N/A', 0, 15), 1, 0, 'L');
            $pdf->Cell($w[4], 6, substr($row['model'] ?? 'N/A', 0, 20), 1, 0, 'L');
            $pdf->Cell($w[5], 6, substr($row['storage'] ?? 'N/A', 0, 15), 1, 0, 'C');
            $pdf->Cell($w[6], 6, '+' . $row['quantity_added'], 1, 0, 'C');
            $pdf->Cell($w[7], 6, $row['previous_stock'], 1, 0, 'C');
            $pdf->Cell($w[8], 6, $row['new_stock'], 1, 1, 'C');
        }
    }
    
    // Summary
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Report Summary:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Total Records: ' . $total_movements, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Total Quantity Added: ' . $total_quantity_added, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Unique Products: ' . $unique_products, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
    
    $pdf->Output('stock_in_report_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
    
} catch (Exception $e) {
    error_log("Error in export_stock_in_report.php: " . $e->getMessage());
    
    // Create error PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('iCenter Inventory System');
    $pdf->SetAuthor('iCenter');
    $pdf->SetTitle('Stock In Report - Error');
    
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Stock In Report - Error', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'An error occurred while generating the report:', 0, 1, 'C');
    $pdf->Cell(0, 10, $e->getMessage(), 0, 1, 'C');
    
    $pdf->Output('stock_in_report_error.pdf', 'D');
}
?> 