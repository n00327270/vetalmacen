/**
 * Validaciones de formularios para VetAlmacén
 * Validaciones del lado del cliente
 * Fecha: 2026-01-23
 */

(function() {
    'use strict';

    // ========================================
    // Validaciones de Productos
    // ========================================
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', function(e) {
            let errors = [];

            // Validar código
            const codigo = document.getElementById('codigo');
            if (codigo && codigo.value.trim().length < 3) {
                errors.push('El código debe tener al menos 3 caracteres');
            }

            // Validar nombre
            const nombre = document.getElementById('nombre');
            if (nombre && nombre.value.trim().length < 3) {
                errors.push('El nombre debe tener al menos 3 caracteres');
            }

            // Validar precio
            const precio = document.getElementById('precio');
            if (precio) {
                const precioValue = parseFloat(precio.value);
                if (isNaN(precioValue) || precioValue < 0) {
                    errors.push('El precio debe ser un número válido mayor o igual a 0');
                }
            }

            // Validar subcategoría
            const subcategoria = document.getElementById('subcategoria_id');
            if (subcategoria && !subcategoria.value) {
                errors.push('Debe seleccionar una subcategoría');
            }

            // Validar imagen (si existe)
            const imagen = document.getElementById('imagen');
            if (imagen && imagen.files.length > 0) {
                const file = imagen.files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!validTypes.includes(file.type)) {
                    errors.push('La imagen debe ser JPG, JPEG o PNG');
                }

                if (file.size > maxSize) {
                    errors.push('La imagen no debe superar los 5MB');
                }
            }

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones de Órdenes de Entrada
    // ========================================
    const formOrdenEntrada = document.getElementById('formOrdenEntrada');
    if (formOrdenEntrada) {
        formOrdenEntrada.addEventListener('submit', function(e) {
            let errors = [];

            // Validar proveedor
            const proveedor = document.getElementById('proveedor_id');
            if (proveedor && !proveedor.value) {
                errors.push('Debe seleccionar un proveedor');
            }

            // Validar sucursal
            const sucursal = document.getElementById('sucursal_id');
            if (sucursal && !sucursal.value) {
                errors.push('Debe seleccionar una sucursal');
            }

            // Validar que haya productos
            const productos = document.querySelectorAll('select[name="productos[]"]');
            if (productos.length === 0) {
                errors.push('Debe agregar al menos un producto');
            }

            // Validar cantidades y precios
            const cantidades = document.querySelectorAll('input[name="cantidades[]"]');
            const precios = document.querySelectorAll('input[name="precios[]"]');

            cantidades.forEach((cantidad, index) => {
                const cantidadValue = parseInt(cantidad.value);
                const precioValue = parseFloat(precios[index].value);

                if (isNaN(cantidadValue) || cantidadValue <= 0) {
                    errors.push(`La cantidad en la fila ${index + 1} debe ser mayor a 0`);
                }

                if (isNaN(precioValue) || precioValue < 0) {
                    errors.push(`El precio en la fila ${index + 1} debe ser mayor o igual a 0`);
                }
            });

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones de Órdenes de Salida
    // ========================================
    const formOrdenSalida = document.getElementById('formOrdenSalida');
    if (formOrdenSalida) {
        formOrdenSalida.addEventListener('submit', function(e) {
            let errors = [];

            // Validar sucursal
            const sucursal = document.getElementById('sucursal_id');
            if (sucursal && !sucursal.value) {
                errors.push('Debe seleccionar una sucursal de origen');
            }

            // Validar tipo de salida
            const tipoSalida = document.getElementById('tipo_salida');
            if (tipoSalida && !tipoSalida.value) {
                errors.push('Debe seleccionar un tipo de salida');
            }

            // Validar que haya productos
            const productos = document.querySelectorAll('select[name="productos[]"]');
            if (productos.length === 0) {
                errors.push('Debe agregar al menos un producto');
            }

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones de Proveedores
    // ========================================
    const formProveedor = document.getElementById('formProveedor');
    if (formProveedor) {
        formProveedor.addEventListener('submit', function(e) {
            let errors = [];

            // Validar razón social
            const razonSocial = document.getElementById('razon_social');
            if (razonSocial && razonSocial.value.trim().length < 3) {
                errors.push('La razón social debe tener al menos 3 caracteres');
            }

            // Validar RUC (11 dígitos)
            const ruc = document.getElementById('ruc');
            if (ruc && ruc.value.trim() !== '') {
                if (!/^\d{11}$/.test(ruc.value)) {
                    errors.push('El RUC debe tener exactamente 11 dígitos numéricos');
                }
            }

            // Validar email
            const email = document.getElementById('email');
            if (email && email.value.trim() !== '') {
                if (!validarEmail(email.value)) {
                    errors.push('El email no tiene un formato válido');
                }
            }

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones de Usuarios
    // ========================================
    const formUsuario = document.getElementById('formUsuario');
    if (formUsuario) {
        formUsuario.addEventListener('submit', function(e) {
            let errors = [];

            // Validar username
            const username = document.getElementById('username');
            if (username) {
                if (username.value.trim().length < 4) {
                    errors.push('El nombre de usuario debe tener al menos 4 caracteres');
                }
                if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                    errors.push('El nombre de usuario solo puede contener letras, números y guiones bajos');
                }
            }

            // Validar contraseña
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');
            
            if (password && password.value.trim() !== '') {
                if (password.value.length < 6) {
                    errors.push('La contraseña debe tener al menos 6 caracteres');
                }

                if (passwordConfirm && password.value !== passwordConfirm.value) {
                    errors.push('Las contraseñas no coinciden');
                }
            }

            // Validar rol
            const rol = document.getElementById('rol_id');
            if (rol && !rol.value) {
                errors.push('Debe seleccionar un rol');
            }

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones de cambio de contraseña
    // ========================================
    const formPassword = document.getElementById('formPassword');
    if (formPassword) {
        formPassword.addEventListener('submit', function(e) {
            let errors = [];

            const passwordActual = document.getElementById('password_actual');
            const passwordNuevo = document.getElementById('password_nuevo');
            const passwordConfirm = document.getElementById('password_confirm');

            // Validar que no estén vacíos
            if (!passwordActual || passwordActual.value.trim() === '') {
                errors.push('Debe ingresar su contraseña actual');
            }

            if (!passwordNuevo || passwordNuevo.value.trim() === '') {
                errors.push('Debe ingresar una nueva contraseña');
            }

            if (!passwordConfirm || passwordConfirm.value.trim() === '') {
                errors.push('Debe confirmar la nueva contraseña');
            }

            // Validar longitud mínima
            if (passwordNuevo && passwordNuevo.value.length < 6) {
                errors.push('La nueva contraseña debe tener al menos 6 caracteres');
            }

            // Validar que coincidan
            if (passwordNuevo && passwordConfirm && passwordNuevo.value !== passwordConfirm.value) {
                errors.push('Las contraseñas nuevas no coinciden');
            }

            // Validar que la nueva sea diferente de la actual
            if (passwordActual && passwordNuevo && passwordActual.value === passwordNuevo.value) {
                errors.push('La nueva contraseña debe ser diferente de la actual');
            }

            // Mostrar errores
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });
    }

    // ========================================
    // Validaciones en tiempo real
    // ========================================

    // Validar RUC en tiempo real
    const rucInputs = document.querySelectorAll('input[name="ruc"]');
    rucInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });

        input.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length !== 11) {
                this.classList.add('is-invalid');
                showInputError(this, 'El RUC debe tener 11 dígitos');
            } else {
                this.classList.remove('is-invalid');
                removeInputError(this);
            }
        });
    });

    // Validar email en tiempo real
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() !== '' && !validarEmail(this.value)) {
                this.classList.add('is-invalid');
                showInputError(this, 'Email no válido');
            } else {
                this.classList.remove('is-invalid');
                removeInputError(this);
            }
        });
    });

    // Validar números positivos
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.hasAttribute('min') && parseFloat(this.value) < parseFloat(this.min)) {
                this.value = this.min;
            }
        });
    });

    // Prevenir valores negativos en campos numéricos
    const positiveInputs = document.querySelectorAll('input[type="number"][min="0"]');
    positiveInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === '-') {
                e.preventDefault();
            }
        });
    });

    // ========================================
    // Funciones auxiliares
    // ========================================

    function showValidationErrors(errors) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                 role="alert" style="z-index: 9999; min-width: 400px; max-width: 500px;">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle-fill"></i> Errores de validación
                </h5>
                <ul class="mb-0">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', errorHtml);
        
        // Auto remove después de 8 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-danger');
            if (alerts.length > 0) {
                const lastAlert = alerts[alerts.length - 1];
                lastAlert.style.opacity = '0';
                setTimeout(() => lastAlert.remove(), 300);
            }
        }, 8000);
    }

    function showInputError(input, message) {
        removeInputError(input);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }

    function removeInputError(input) {
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    function validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // ========================================
    // Validación de formularios con Bootstrap
    // ========================================
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    console.log('✅ Validaciones inicializadas correctamente');
})();