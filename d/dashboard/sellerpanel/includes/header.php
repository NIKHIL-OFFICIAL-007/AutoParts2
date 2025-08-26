<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
  <meta charset="UTF-8" />
  <title><?php echo isset($page_title) ? $page_title : 'AutoParts Seller Portal'; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1abc9c',
            'primary-dark': '#16a085',
            secondary: '#3498db',
            dark: '#2c3e50',
            light: '#ecf0f1',
            gray: '#95a5a6',
            danger: '#e74c3c',
            success: '#2ecc71',
          },
          fontFamily: {
            inter: ['Inter', 'sans-serif'],
          },
          animation: {
            fadeIn: 'fadeIn 0.5s ease forwards',
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0', transform: 'translateY(20px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' },
            }
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .form-input:focus {
        border-color: #1abc9c;
        background-color: #fff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.15);
      }
      .file-upload:hover {
        border-color: #1abc9c;
        background: rgba(26, 188, 156, 0.05);
      }
    }
  </style>
</head>
<body class="font-inter bg-gradient-to-br from-[#f5f7fa] to-[#e4efe9] min-h-screen flex text-gray-800 leading-relaxed">