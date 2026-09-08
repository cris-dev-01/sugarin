describe('Flujo de registro de glucosa', () => {
    beforeEach(() => {
        cy.login('e2e-admin@example.org', 'password');
        cy.visit('/glucose-logs');
    });

    function captureReading(value) {
        // Con un único paciente disponible, el flujo avanza automáticamente
        // desde la selección de paciente hasta la captura OCR.
        cy.contains('h2', 'Capturar lectura', { timeout: 15000 }).should('be.visible');

        cy.get('input[type="file"]').selectFile('cypress/fixtures/glucose-reading.png', { force: true });

        // Tesseract.js procesa la imagen (descarga de motor OCR + reconocimiento);
        // se le da un margen amplio antes de llegar al paso de confirmación.
        cy.contains('h2', 'Confirmar lectura', { timeout: 60000 }).should('be.visible');

        cy.get('input[type="number"]').clear().type(String(value));
        cy.contains('button', 'Registrar lectura').click();

        cy.contains('h2', 'Lectura registrada', { timeout: 15000 }).should('be.visible');
    }

    it('marca la lectura como dentro de rango normal', () => {
        captureReading(90);

        cy.contains('Lectura dentro del rango normal').should('be.visible');
        cy.contains('span', '90').should('have.class', 'text-primary');
        cy.contains('span', 'Rango normal').should('have.class', 'badge-outline-success');

        // El último paso del indicador (StepsIndicator) queda marcado como
        // completado (verde), no "activo" (azul), al llegar a esta pantalla.
        cy.get('.flex.items-start.w-full').find('.rounded-full').last().should('have.class', 'bg-success');
    });

    it('marca la lectura elevada como fuera de rango', () => {
        captureReading(180);

        cy.contains('Lectura fuera de rango').should('be.visible');
        cy.contains('Rango esperado').should('be.visible');
        cy.contains('span', '180').should('have.class', 'text-danger');
        cy.contains('span', 'Elevado - fuera de rango normal').should('have.class', 'badge-outline-danger');
    });

    it('marca la lectura baja como fuera de rango con color de advertencia', () => {
        captureReading(50);

        cy.contains('Lectura fuera de rango').should('be.visible');
        cy.contains('span', '50').should('have.class', 'text-warning');
        cy.contains('span', 'Bajo - fuera de rango normal').should('have.class', 'badge-outline-warning');
    });
});
