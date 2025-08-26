                </main> <!-- Close main content area from header.php -->
                
                <!-- Footer -->
                <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <div class="container mx-auto px-6 py-4">
                        <div class="flex flex-col md:flex-row items-center justify-between">
                            <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    &copy; <?php echo date('Y'); ?> AdminPanel. All rights reserved.
                                </p>
                                <div class="hidden md:block h-4 w-px bg-gray-300 dark:bg-gray-600"></div>
                                <div class="flex space-x-4">
                                    <a href="#" class="text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Privacy Policy</a>
                                    <a href="#" class="text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Terms of Service</a>
                                    <a href="#" class="text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Help Center</a>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                                <span class="text-sm text-gray-500 dark:text-gray-400">v2.1.0</span>
                                <div class="flex space-x-3">
                                    <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                        <i class="fab fa-github text-lg"></i>
                                    </a>
                                    <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                        <i class="fab fa-twitter text-lg"></i>
                                    </a>
                                    <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                        <i class="fab fa-facebook text-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                
                <!-- Quick Action Buttons -->
                <div class="fixed bottom-6 right-6 flex flex-col space-y-3 z-40">
                    <button onclick="window.location.href='create_order.php'" 
                            class="bg-primary-600 text-white p-4 rounded-full shadow-lg hover:bg-primary-700 transition-all transform hover:scale-110 group">
                        <i class="fas fa-plus text-xl"></i>
                        <span class="absolute right-full mr-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            New Order
                        </span>
                    </button>
                    
                    <button onclick="window.location.href='add_user.php'" 
                            class="bg-green-600 text-white p-4 rounded-full shadow-lg hover:bg-green-700 transition-all transform hover:scale-110 group">
                        <i class="fas fa-user-plus text-xl"></i>
                        <span class="absolute right-full mr-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            Add User
                        </span>
                    </button>
                    
                    <button id="feedbackButton" 
                            class="bg-purple-600 text-white p-4 rounded-full shadow-lg hover:bg-purple-700 transition-all transform hover:scale-110 group">
                        <i class="fas fa-comment-dots text-xl"></i>
                        <span class="absolute right-full mr-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            Send Feedback
                        </span>
                    </button>
                </div>
                
                <!-- Feedback Modal -->
                <div id="feedbackModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Send Feedback</h3>
                            <button id="closeFeedbackModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="px-6 py-4">
                            <form id="feedbackForm">
                                <div class="mb-4">
                                    <label for="feedbackType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                                    <select id="feedbackType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option>Bug Report</option>
                                        <option>Feature Request</option>
                                        <option>General Feedback</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="feedbackMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                                    <textarea id="feedbackMessage" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="Your feedback..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                            <button id="cancelFeedback" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button id="submitFeedback" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
                                Submit Feedback
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- JavaScript Libraries -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                
                <script>
                    // Feedback modal handling
                    document.getElementById('feedbackButton').addEventListener('click', () => {
                        document.getElementById('feedbackModal').classList.remove('hidden');
                    });
                    
                    document.getElementById('closeFeedbackModal').addEventListener('click', () => {
                        document.getElementById('feedbackModal').classList.add('hidden');
                    });
                    
                    document.getElementById('cancelFeedback').addEventListener('click', () => {
                        document.getElementById('feedbackModal').classList.add('hidden');
                    });
                    
                    document.getElementById('submitFeedback').addEventListener('click', () => {
                        const type = document.getElementById('feedbackType').value;
                        const message = document.getElementById('feedbackMessage').value;
                        
                        if (message.trim() === '') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Please enter your feedback message!',
                            });
                            return;
                        }
                        
                        // Simulate AJAX submission
                        setTimeout(() => {
                            document.getElementById('feedbackModal').classList.add('hidden');
                            document.getElementById('feedbackForm').reset();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Thank you!',
                                text: 'Your feedback has been submitted.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }, 800);
                    });
                    
                    // Close modal when clicking outside
                    document.getElementById('feedbackModal').addEventListener('click', (e) => {
                        if (e.target === document.getElementById('feedbackModal')) {
                            document.getElementById('feedbackModal').classList.add('hidden');
                        }
                    });
                    
                    // Keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        // Ctrl+Shift+F for feedback
                        if (e.ctrlKey && e.shiftKey && e.key === 'F') {
                            e.preventDefault();
                            document.getElementById('feedbackModal').classList.remove('hidden');
                        }
                        
                        // Escape to close modals
                        if (e.key === 'Escape') {
                            document.getElementById('feedbackModal').classList.add('hidden');
                        }
                    });
                </script>
            </div> <!-- Close flex container from header.php -->
        </div> <!-- Close content area from header.php -->
    </body>
</html>