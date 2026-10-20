const tableBody = document.getElementById('feeTableBody');
const payBtn = document.getElementById('payNowBtn');

let feesData = [];

async function loadFees() {
  try {
    const response = await fetch('../data/fees.json'); 

    if (!response.ok) {
      throw new Error(`Failed to load fees: ${response.status}`);
    }

    feesData = await response.json();
    renderFeeTable(feesData);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="4">Could not load fee data.</td></tr>`;
    console.error(error);
  }
}

function renderFeeTable(fees) {
  tableBody.innerHTML = '';

  fees.forEach(fee => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${fee.term}</td>
      <td>₹${fee.amountDue.toLocaleString('en-IN')}</td>
      <td>₹${fee.amountPaid.toLocaleString('en-IN')}</td>
      <td class="status-${fee.status.toLowerCase()}">${fee.status}</td>
    `;
    tableBody.appendChild(row);
  });
}

async function payNow() {
  const unpaidFee = feesData.find(f => f.status === 'Unpaid');

  if (!unpaidFee) {
    alert('All fees are already paid!');
    return;
  }

  payBtn.disabled = true;
  payBtn.textContent = 'Processing...';

  try {
    const result = await mockPaymentRequest(unpaidFee);

    if (result.success) {
      unpaidFee.amountPaid = unpaidFee.amountDue;
      unpaidFee.status = 'Paid';

      renderFeeTable(feesData);

      alert(`Payment successful for ${unpaidFee.term}!`);
    }
  } catch (error) {
    alert('Payment failed. Please try again.');
    console.error(error);
  } finally {
    payBtn.disabled = false;
    payBtn.textContent = 'Pay Now';
  }
}

function mockPaymentRequest(fee) {
  return new Promise(resolve => {
    setTimeout(() => {
      resolve({ success: true });
    }, 1000);
  });
}

payBtn.addEventListener('click', payNow);
loadFees();