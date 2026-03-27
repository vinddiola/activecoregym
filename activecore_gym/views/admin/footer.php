<?php
/**
 * Admin Footer - Common footer for all admin pages
 */
?>
    </div> <!-- End container -->

    <style>
        /* Additional footer styles for black and gold theme */
        .text-muted {
            color: var(--dark-gold) !important;
        }
        
        .text-secondary {
            color: var(--primary-gold) !important;
        }
        
        .border {
            border-color: var(--dark-gold) !important;
        }
        
        .border-top {
            border-top-color: var(--dark-gold) !important;
        }
        
        .border-bottom {
            border-bottom-color: var(--dark-gold) !important;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark-gray);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--dark-gold);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-gold);
        }
        
        /* Special card backgrounds */
        .bg-success {
            background-color: var(--dark-gold) !important;
            color: var(--light-gold) !important;
        }
        
        .bg-warning {
            background-color: var(--primary-gold) !important;
            color: var(--black-bg) !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
            color: var(--light-gold) !important;
        }
        
        .bg-info {
            background-color: #17a2b8 !important;
            color: var(--light-gold) !important;
        }
        
        /* Links */
        a {
            color: var(--primary-gold);
        }
        
        a:hover {
            color: var(--dark-gold);
        }
        
        /* Text colors */
        h1, h2, h3, h4, h5, h6 {
            color: var(--primary-gold);
        }
        
        /* Button variations */
        .btn-success {
            background-color: var(--dark-gold);
            border-color: var(--primary-gold);
            color: var(--light-gold);
        }
        
        .btn-success:hover {
            background-color: var(--primary-gold);
            color: var(--black-bg);
        }
        
        .btn-warning {
            background-color: var(--primary-gold);
            border-color: var(--dark-gold);
            color: var(--black-bg);
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: var(--light-gold);
        }
        
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: var(--light-gold);
        }
        
        .btn-outline-success {
            border-color: var(--dark-gold);
            color: var(--dark-gold);
        }
        
        .btn-outline-success:hover {
            background-color: var(--dark-gold);
            color: var(--light-gold);
        }
        
        .btn-outline-warning {
            border-color: var(--primary-gold);
            color: var(--primary-gold);
        }
        
        .btn-outline-warning:hover {
            background-color: var(--primary-gold);
            color: var(--black-bg);
        }
        
        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: var(--light-gold);
        }
        
        .btn-outline-info {
            border-color: #17a2b8;
            color: #17a2b8;
        }
        
        .btn-outline-info:hover {
            background-color: #17a2b8;
            color: var(--light-gold);
        }
    </style>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/admin.js"></script>
    
    <!-- Page-specific scripts can be added here -->
    <?php if (isset($page_scripts)): ?>
        <?php echo $page_scripts; ?>
    <?php endif; ?>
</body>
</html>
