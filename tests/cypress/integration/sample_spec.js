describe('Test Admin Login Page', function() {
	beforeEach(() => {
		Cypress.Cookies.defaults();
		Cypress.Cookies.preserveOnce();
	});

	const params = {
		adminUrl: '/wp-admin',
		adminUser: 'admin',
		adminPass: 'password'
	};

	Cypress.Commands.add('login', function() {
		cy.visit(params.adminUrl).url();

		cy.get('#user_login').type(params.adminUser);

		cy.get('#user_pass').type(params.adminPass);

		cy.get('#wp-submit').click();
	});

	it('Logs into admin', function() {
		cy.login();
	});
});
