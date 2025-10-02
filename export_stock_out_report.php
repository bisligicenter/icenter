<?php
session_start();
require_once 'db.php';
require_once 'tcpdf/tcpdf.php';

try {
    $pdo = getConnection();
    
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    // Query to get stock out records from stock_out table with enhanced data
    $query = "
        SELECT 
            so.id as movement_id,
            DATE_FORMAT(so.created_at, '%Y-%m-%d %H:%i:%s') as date_time,
            DATE(so.created_at) as date,
            p.product_id,
            p.product as product_name,
            p.brand,
            p.model,
            p.storage,
            ABS(so.quantity) as quantity_removed,
            so.previous_stock,
            so.new_stock,
            so.created_by as removed_by,
            COALESCE(so.notes, 'No notes provided') as reason,
            p.stock_quantity as current_stock
        FROM stock_out so
        JOIN products p ON so.product_id = p.product_id
        WHERE DATE(so.created_at) BETWEEN :start_date AND :end_date
        ORDER BY so.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if stock_out table exists
    $table_check_query = "SHOW TABLES LIKE 'stock_out'";
    $table_check_stmt = $pdo->prepare($table_check_query);
    $table_check_stmt->execute();
    
    if ($table_check_stmt->rowCount() == 0) {
        // Table doesn't exist, create a PDF with error message
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('iCenter Inventory System');
        $pdf->SetAuthor('iCenter');
        $pdf->SetTitle('Stock Out Report - Setup Required');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Stock Out Report', 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Stock out tracking is not set up.', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Please run the database setup first.', 0, 1, 'C');
        
        $pdf->Output('stock_out_report.pdf', 'D');
        exit;
    }
    
    // Create PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('iCenter Inventory System');
    $pdf->SetAuthor('iCenter');
    $pdf->SetTitle('Stock Out Report');
    $pdf->SetSubject('Stock Out Report from ' . $start_date . ' to ' . $end_date);
    
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
    $pdf->Cell(0, 10, 'Stock Out Report', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Date range
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Period: ' . date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    // Define column widths
    $w = array(25, 20, 40, 25, 30, 25, 25, 25, 30, 30);
    
    // Header row
    $pdf->Cell($w[0], 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell($w[1], 7, 'ID', 1, 0, 'C', true);
    $pdf->Cell($w[2], 7, 'Product', 1, 0, 'C', true);
    $pdf->Cell($w[3], 7, 'Brand', 1, 0, 'C', true);
    $pdf->Cell($w[4], 7, 'Model', 1, 0, 'C', true);
    $pdf->Cell($w[5], 7, 'Qty Removed', 1, 0, 'C', true);
    $pdf->Cell($w[6], 7, 'Prev Stock', 1, 0, 'C', true);
    $pdf->Cell($w[7], 7, 'New Stock', 1, 0, 'C', true);
    $pdf->Cell($w[8], 7, 'Removed By', 1, 0, 'C', true);
    $pdf->Cell($w[9], 7, 'Reason', 1, 1, 'C', true);
    
    // Data rows
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetFillColor(255, 255, 255);
    
    if (empty($results)) {
        $pdf->Cell(array_sum($w), 10, 'No stock out records found for the selected date range', 1, 1, 'C');
    } else {
        foreach ($results as $row) {
            $pdf->Cell($w[0], 6, $row['date'], 1, 0, 'C');
            $pdf->Cell($w[1], 6, $row['movement_id'], 1, 0, 'C');
            $pdf->Cell($w[2], 6, substr($row['product_name'], 0, 20), 1, 0, 'L');
            $pdf->Cell($w[3], 6, substr($row['brand'], 0, 15), 1, 0, 'L');
            $pdf->Cell($w[4], 6, substr($row['model'], 0, 20), 1, 0, 'L');
            $pdf->Cell($w[5], 6, '-' . $row['quantity_removed'], 1, 0, 'C');
            $pdf->Cell($w[6], 6, $row['previous_stock'], 1, 0, 'C');
            $pdf->Cell($w[7], 6, $row['new_stock'], 1, 0, 'C');
            $pdf->Cell($w[8], 6, substr($row['removed_by'], 0, 20), 1, 0, 'L');
            $pdf->Cell($w[9], 6, substr($row['reason'], 0, 20), 1, 1, 'L');
        }
    }
    
    // Summary
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Summary:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Total Records: ' . count($results), 0, 1, 'L');
    
    if (!empty($results)) {
        $total_quantity = array_sum(array_column($results, 'quantity_removed'));
        $pdf->Cell(0, 6, 'Total Quantity Removed: ' . $total_quantity, 0, 1, 'L');
    }
    
    // Output PDF
    $pdf->Output('stock_out_report_' . $start_date . '_to_' . $end_date . '.pdf', 'D');
    
} catch (Exception $e) {
    error_log("Error in export_stock_out_report.php: " . $e->getMessage());
    
    // Create error PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('iCenter Inventory System');
    $pdf->SetAuthor('iCenter');
    $pdf->SetTitle('Stock Out Report - Error');
    
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Stock Out Report - Error', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'An error occurred while generating the report:', 0, 1, 'C');
    $pdf->Cell(0, 10, $e->getMessage(), 0, 1, 'C');
    
    $pdf->Output('stock_out_report_error.pdf', 'D');
}
?> 