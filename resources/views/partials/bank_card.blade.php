<script src="https://js.stripe.com/v3/"></script>
<div class="overlay" id="overlay">
    <div class="card bank">

        <div style="display: flex; align-items: center; justify-content: center">
            
            <h2 style="padding: 10px;margin:0">Add credit to your account</h2>
        </div>

        <form id="payment-form">
            @csrf
            <div style="display: flex; padding: 10px; align-items: center; white-space: nowrap">
                <label for="amount" style="margin-bottom: 0; margin-right: 10px" >Amount (€):</label>
                <input type="number" name="amount" id="amount" style="margin-bottom: 0" required>
            </div>

            <!-- Stripe Card Element -->
            <div id="card-element" style="margin: 1em"></div>
            <div id="card-errors" role="alert" style="color: red;"></div>

            
        </form>
            <div style="display: flex; justify-content: space-between">
                <button class="close-btn" id="closeCard">Cancel</button>
                <button type="button" id="submit-button">Pay</button>
            </div>
        <div id="payment-status" style="margin-top: 20px; display: none;">
            <p id="status-message"></p>
        </div>
    </div>
</div>

<script>
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    const cancelButton = document.getElementById('closeCard');
    const submitButton = document.getElementById('submit-button');
    const statusMessage = document.getElementById('status-message');
    const statusDiv = document.getElementById('payment-status');
    const cardErrors = document.getElementById('card-errors');

    submitButton.addEventListener('click', async (event) => {
        event.preventDefault();
        submitButton.disabled = true;

        // Clear previous errors
        cardErrors.textContent = '';
        statusDiv.style.display = 'none';

        // Create Stripe token
        const { token, error } = await stripe.createToken(card);

        if (error) {
            // Display error
            cardErrors.textContent = error.message;
            submitButton.disabled = false;
            return;
        }

        // Send token and amount to backend
        const amount = document.getElementById('amount').value;
        const response = await fetch("{{ route('payment.process') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify({
                stripeToken: token.id,
                amount: amount,
            }),
        });

        const result = await response.json();

        console.log("POPO")



        


        if (result.success) {
            statusDiv.style.display = 'block';
            statusMessage.textContent = 'Payment successful!';
            statusMessage.style.color = 'green';

            const creditDiv = document.querySelector('.credit');
            console.log(creditDiv.innerText)
            console.log(result.charge_amount)
            const newCredit = parseInt(creditDiv.innerText) + parseInt(result.charge_amount);
            creditDiv.innerText = newCredit
            console.log(creditDiv.innerText)

        } else {
            statusDiv.style.display = 'block';
            statusMessage.textContent = `Payment failed: ${result.error}`;
            statusMessage.style.color = 'red';
        }

        submitButton.disabled = false;

    });

    // Reset the form and card element when the card is closed
    cancelButton.addEventListener('click', () => {
        cardErrors.textContent = '';  // Clear any error messages
        
        
        // Optionally hide the overlay
        document.getElementById('overlay').style.display = 'none';
    });
</script>
