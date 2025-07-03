<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <h1>Payment Page</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <form action="{{ route('payment.process') }}" method="POST" id="payment-form">
        @csrf
        <label for="amount">Amount (USD):</label>
        <input type="number" name="amount" id="amount" required>
        
        <div id="card-element"><!-- Stripe.js will embed the card input field here --></div>

        <button type="submit">Pay</button>
    </form>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const { token, error } = await stripe.createToken(card);
            if (error) {
                console.error(error.message);
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    </script>
</body>
</html>
