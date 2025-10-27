    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <!-- Información de la tienda -->
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-bicycle"></i> Bike Store</h5>
                    <p class="text-muted">Tu tienda de confianza para bicicletas y accesorios de alta calidad.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                
                <!-- Enlaces rápidos -->
                <div class="col-md-2 mb-4">
                    <h6>Compra</h6>
                    <ul class="list-unstyled">
                        <li><a href="/Bike_Store/cliente/pages/catalogo.php" class="text-muted text-decoration-none">Productos</a></li>
                        <li><a href="/Bike_Store/cliente/pages/carrito.php" class="text-muted text-decoration-none">Carrito</a></li>
                        <li><a href="/Bike_Store/cliente/pages/mis_pedidos.php" class="text-muted text-decoration-none">Mis Pedidos</a></li>
                    </ul>
                </div>
                
                <!-- Servicio al cliente -->
                <div class="col-md-3 mb-4">
                    <h6>Servicio al Cliente</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Envíos</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Devoluciones</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Garantías</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Preguntas Frecuentes</a></li>
                    </ul>
                </div>
                
                <!-- Contacto -->
                <div class="col-md-3 mb-4">
                    <h6>Contacto</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-map-marker-alt"></i> Av. Principal 123, Ciudad</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@bikestore.com</li>
                        <li><i class="fas fa-clock"></i> Lun-Vie: 9AM-6PM</li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-secondary">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <small>&copy; <?php echo date('Y'); ?> Bike Store. Todos los derechos reservados.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>
                        <a href="#" class="text-muted text-decoration-none">Términos y Condiciones</a> | 
                        <a href="#" class="text-muted text-decoration-none">Política de Privacidad</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle JS -->
    <script src="/Bike_Store/assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para actualizar contador del carrito -->
    <script>
        // Actualizar contador del carrito al cargar la página
        function actualizarContadorCarrito() {
            fetch('/Bike_Store/api/carrito_get.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const totalItems = data.items.reduce((sum, item) => sum + parseInt(item.quantity), 0);
                        document.getElementById('cart-count').textContent = totalItems;
                    }
                })
                .catch(error => console.error('Error al actualizar carrito:', error));
        }
        
        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', actualizarContadorCarrito);
    </script>
</body>
</html>
