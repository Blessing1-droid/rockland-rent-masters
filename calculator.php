<?php
/**
 * calculator.php - Rent Affordability Calculator
 * PRD v1.1: Interactive rent affordability calculator tool
 */

$pageTitle = "Rent Affordability Calculator - How Much Rent Can You Afford?";
$pageDescription = "Calculate how much rent you can afford based on your income, expenses, and financial goals.";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1>💰 Rent Affordability Calculator</h1>
        <p class="text-muted">Find out how much rent you can comfortably afford based on your financial situation.</p>
    </div>
    
    <div class="calculator-container" style="background: var(--bg-white); border-radius: 16px; padding: 2rem; box-shadow: var(--shadow-md);">
        <form id="calculator-form">
            <div class="form-group">
                <label class="form-label">Monthly Gross Income (before taxes)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">$</span>
                    <input type="number" id="monthly-income" class="form-input" style="padding-left: 30px;" placeholder="e.g., 5000" required>
                </div>
                <small class="form-help">Your total monthly income from all sources</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Monthly Debt Payments</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">$</span>
                    <input type="number" id="monthly-debts" class="form-input" style="padding-left: 30px;" placeholder="e.g., 500" value="0">
                </div>
                <small class="form-help">Student loans, car payments, credit cards, etc.</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Monthly Savings Goal</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">$</span>
                    <input type="number" id="monthly-savings" class="form-input" style="padding-left: 30px;" placeholder="e.g., 500" value="0">
                </div>
                <small class="form-help">How much you want to save each month</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Recommended Rent Rule</label>
                <select id="rule-type" class="form-select">
                    <option value="30">30% Rule (Standard)</option>
                    <option value="28">28% Rule (Conservative)</option>
                    <option value="35">35% Rule (Flexible)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Calculate Affordability →</button>
        </form>
        
        <div id="results" style="margin-top: 2rem; display: none;"></div>
    </div>
    
    <!-- Additional Information -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 12px;">
        <h3>📖 Understanding the 30% Rule</h3>
        <p>The standard recommendation is to spend no more than 30% of your gross monthly income on rent. This leaves enough room for other expenses like utilities, groceries, transportation, and savings.</p>
        
        <h3 style="margin-top: 1rem;">💡 Tips for Renters</h3>
        <ul>
            <li>Consider utilities, parking, and renters insurance in your total housing budget</li>
            <li>Look for properties with included amenities to save money</li>
            <li>Consider a roommate to split costs if needed</li>
            <li>Don't forget about security deposits and moving costs</li>
        </ul>
        
        <h3 style="margin-top: 1rem;">📊 Budget Breakdown Example</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 8px;">Rent (30%)</td>
                <td style="padding: 8px; text-align: right;">$1,500</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 8px;">Utilities (10%)</td>
                <td style="padding: 8px; text-align: right;">$500</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 8px;">Food (15%)</td>
                <td style="padding: 8px; text-align: right;">$750</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 8px;">Transportation (10%)</td>
                <td style="padding: 8px; text-align: right;">$500</td>
            </tr>
            <tr>
                <td style="padding: 8px;">Savings & Other (35%)</td>
                <td style="padding: 8px; text-align: right;">$1,750</td>
            </tr>
        </table>
        <p style="margin-top: 1rem; font-size: 0.875rem; color: #666;">*Based on $5,000 monthly income</p>
    </div>
</div>

<style>
.form-help {
    display: block;
    font-size: 0.75rem;
    color: var(--text-light);
    margin-top: 0.25rem;
}
.result-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}
.result-amount {
    font-size: 3rem;
    font-weight: bold;
    margin: 0.5rem 0;
}
.result-range {
    font-size: 1.25rem;
    margin: 0.5rem 0;
}
.disclaimer {
    font-size: 0.75rem;
    color: #666;
    text-align: center;
    margin-top: 1rem;
}
</style>

<script>
document.getElementById('calculator-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const monthlyIncome = parseFloat(document.getElementById('monthly-income').value) || 0;
    const monthlyDebts = parseFloat(document.getElementById('monthly-debts').value) || 0;
    const monthlySavings = parseFloat(document.getElementById('monthly-savings').value) || 0;
    const rulePercentage = parseFloat(document.getElementById('rule-type').value) / 100;
    
    if (monthlyIncome <= 0) {
        showToast('Please enter your monthly income', 'error');
        return;
    }
    
    // Calculate maximum affordable rent
    const maxRentByIncome = monthlyIncome * rulePercentage;
    const maxRentByBudget = monthlyIncome - monthlyDebts - monthlySavings - (monthlyIncome * 0.2); // 20% for other expenses
    const recommendedRent = Math.min(maxRentByIncome, maxRentByBudget);
    const minRent = monthlyIncome * 0.2;
    const maxRent = maxRentByIncome;
    
    // Calculate year salary
    const annualSalary = monthlyIncome * 12;
    
    // Display results
    const resultsDiv = document.getElementById('results');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = `
        <div class="result-card">
            <div style="opacity: 0.9;">You can afford approximately</div>
            <div class="result-amount">$${Math.round(recommendedRent).toLocaleString()}/month</div>
            <div class="result-range">Range: $${Math.round(minRent).toLocaleString()} - $${Math.round(maxRent).toLocaleString()}/month</div>
            <div style="margin-top: 1rem; font-size: 0.875rem;">
                Based on ${rulePercentage * 100}% of your $${monthlyIncome.toLocaleString()} monthly income
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; padding: 1rem; background: #f0f8ff; border-radius: 8px;">
            <h4 style="margin-bottom: 0.5rem;">📊 Financial Summary</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 0.5rem;">💰 Annual Income: <strong>$${annualSalary.toLocaleString()}</strong></li>
                <li style="margin-bottom: 0.5rem;">💳 Monthly Debt Payments: <strong>$${monthlyDebts.toLocaleString()}</strong></li>
                <li style="margin-bottom: 0.5rem;">🏦 Monthly Savings Goal: <strong>$${monthlySavings.toLocaleString()}</strong></li>
                <li style="margin-bottom: 0.5rem;">📋 Remaining for Other Expenses: <strong>$${Math.round(monthlyIncome - recommendedRent - monthlyDebts - monthlySavings).toLocaleString()}</strong></li>
            </ul>
        </div>
        
        <div class="disclaimer">
            ⚠️ This is an estimate. Your actual affordable rent may vary based on location, utilities, and lifestyle choices.
            Always consult with a financial advisor for personalized advice.
        </div>
    `;
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>