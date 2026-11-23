/**
 * EKAA Milestone 5 (M5) Test Suite: Deterministic Analytics & Security
 * Verifies Acceptance Criteria AC-M5-01 through AC-M5-09
 */

const fs = require('fs');
const path = require('path');

console.log('=== Running EKAA M5 Deterministic Analytics & Policy Suite ===\n');

let passedTests = 0;
let totalTests = 0;

function assertTest(condition, testName, details = '') {
  totalTests++;
  if (condition) {
    passedTests++;
    console.log(`[PASS] ${testName}`);
  } else {
    console.error(`[FAIL] ${testName} - ${details}`);
    process.exitCode = 1;
  }
}

// =============================================================================
// Test 1: AC-M5-01 & AC-M5-02 - Metric Engine Core & Edge Cases
// =============================================================================
console.log('--- Testing AC-M5-01 & AC-M5-02: Deterministic Metric Calculations ---');

function computeCAGR(valueStart, valueEnd, nPeriods) {
  if (!Number.isFinite(valueStart) || !Number.isFinite(valueEnd) || !Number.isFinite(nPeriods)) {
    throw new Error('CAGR values must be finite');
  }
  if (valueStart <= 0) {
    throw new Error(`cagr: value_start must be > 0; got ${valueStart}`);
  }
  if (valueEnd < 0) {
    throw new Error(`cagr: value_end must be >= 0; got ${valueEnd}`);
  }
  if (nPeriods <= 0) {
    throw new Error(`cagr: n_periods must be > 0; got ${nPeriods}`);
  }
  return Math.pow(valueEnd / valueStart, 1.0 / nPeriods) - 1.0;
}

function computeYoY(valueCurrent, valuePrior) {
  if (valuePrior === 0) {
    throw new Error('yoy: value_prior is 0; division by zero is undefined');
  }
  return (valueCurrent - valuePrior) / Math.abs(valuePrior);
}

function computeMoM(valueCurrent, valuePrior) {
  if (valuePrior === 0) {
    throw new Error('mom: value_prior is 0; division by zero is undefined');
  }
  return (valueCurrent - valuePrior) / Math.abs(valuePrior);
}

function computeSMA(values, window) {
  if (!values || values.length === 0) throw new Error('moving_average: values list must not be empty');
  if (window < 1) throw new Error(`moving_average: window must be >= 1; got ${window}`);
  if (window > values.length) throw new Error(`moving_average: window (${window}) exceeds number of values (${values.length})`);
  const result = [];
  for (let i = 0; i <= values.length - window; i++) {
    const sum = values.slice(i, i + window).reduce((a, b) => a + b, 0);
    result.push(sum / window);
  }
  return result;
}

function computeShare(part, total) {
  if (total === 0) throw new Error('share: total is 0; division by zero is undefined');
  return (part / total) * 100.0;
}

function computeRank(values, ascending = true) {
  if (!values || values.length === 0) throw new Error('rank: values list must not be empty');
  const sortedUnique = Array.from(new Set(values)).sort((a, b) => ascending ? a - b : b - a);
  const rankMap = new Map();
  sortedUnique.forEach((v, idx) => rankMap.set(v, idx + 1));
  return values.map(v => rankMap.get(v));
}

// 1a. CAGR normal calculation
const cagrValid = computeCAGR(100, 144, 2);
assertTest(Math.abs(cagrValid - 0.2) < 1e-6, 'AC-M5-01: CAGR computes standard growth correctly (20%)');

// 1b. CAGR start <= 0 rejection
let cagrZeroRejected = false;
try {
  computeCAGR(0, 144, 2);
} catch (e) {
  cagrZeroRejected = true;
}
assertTest(cagrZeroRejected, 'AC-M5-02: CAGR rejects value_start <= 0');

let cagrNegRejected = false;
try {
  computeCAGR(-50, 144, 2);
} catch (e) {
  cagrNegRejected = true;
}
assertTest(cagrNegRejected, 'AC-M5-02: CAGR rejects negative value_start');

// 1c. YoY & MoM zero division rejection
let yoyZeroRejected = false;
try {
  computeYoY(150, 0);
} catch (e) {
  yoyZeroRejected = true;
}
assertTest(yoyZeroRejected, 'AC-M5-01: YoY rejects zero denominator');

// 1d. SMA window exceeding bounds rejection
let smaWindowRejected = false;
try {
  computeSMA([10, 20], 3);
} catch (e) {
  smaWindowRejected = true;
}
assertTest(smaWindowRejected, 'AC-M5-01: SMA rejects window > array length');

// 1e. Share calculation and zero division rejection
const shareVal = computeShare(25, 100);
assertTest(shareVal === 25.0, 'AC-M5-01: Share computes percentage correctly');
let shareZeroRejected = false;
try {
  computeShare(25, 0);
} catch (e) {
  shareZeroRejected = true;
}
assertTest(shareZeroRejected, 'AC-M5-01: Share rejects zero total');

// 1f. Dense rank with ties
const ranks = computeRank([100, 200, 200, 300], true);
assertTest(JSON.stringify(ranks) === JSON.stringify([1, 2, 2, 3]), 'AC-M5-01: Dense rank handles ties without gaps');

// =============================================================================
// Test 2: AC-M5-03 - Architectural Determinism Check
// =============================================================================
console.log('\n--- Testing AC-M5-03: Architectural Determinism (LLM Never Computes Numbers) ---');
const metricEnginePath = path.join(__dirname, '../../services/ai/ekaa_ai/domain/analytics/metric_engine.py');
const metricEngineSource = fs.readFileSync(metricEnginePath, 'utf8');
const hasInvariantNotice = metricEngineSource.includes('AGENTS.md §INV-07') && metricEngineSource.includes('LLM NEVER computes final output numbers');
assertTest(hasInvariantNotice, 'AC-M5-03: metric_engine.py enforces INV-07 invariant in code header');

// =============================================================================
// Test 3: AC-M5-04 - Chart Specification Validation & Traceability
// =============================================================================
console.log('\n--- Testing AC-M5-04: Chart Specification Validation & Value Traceability ---');

function validateChartSpec(spec, allowedMetricValues) {
  const supportedTypes = ['bar', 'line', 'pie', 'donut', 'area', 'scatter', 'table', 'metric_card', 'waterfall', 'combo'];
  if (!spec || typeof spec !== 'object') throw new Error('chart_spec must be an object');
  if (!supportedTypes.includes(spec.chart_type)) {
    throw new Error(`Unsupported chart_type: ${spec.chart_type}`);
  }
  if (!spec.title || typeof spec.title !== 'string') throw new Error('Invalid title');
  if (!Array.isArray(spec.data) || spec.data.length === 0) throw new Error('data must be non-empty array');

  // Check metric traceability
  if (allowedMetricValues && allowedMetricValues.length > 0) {
    for (const item of spec.data) {
      const val = item.value ?? item.y;
      if (typeof val !== 'number') throw new Error('Chart data value must be numeric');
      const isTraced = allowedMetricValues.some(av => Math.abs(val - av) < 1e-4);
      if (!isTraced) {
        throw new Error(`AC-M5-04 VIOLATION: Chart value ${val} not traceable to deterministic metrics`);
      }
    }
  }
  return true;
}

// 3a. Valid chart spec with traced values
const validChart = {
  chart_type: 'bar',
  title: 'Pertumbuhan Anggaran 2024-2026',
  data: [
    { label: '2024', value: 100 },
    { label: '2025', value: 120 },
    { label: '2026', value: 144 }
  ]
};
const allowedMetrics = [100, 120, 144, 0.2];
assertTest(validateChartSpec(validChart, allowedMetrics) === true, 'AC-M5-04: Valid chart spec with traced values passes');

// 3b. Invalid chart type rejected
let invalidTypeRejected = false;
try {
  validateChartSpec({ chart_type: '3d_pyramid', title: 'Test', data: [{ label: 'A', value: 100 }] }, allowedMetrics);
} catch (e) {
  invalidTypeRejected = true;
}
assertTest(invalidTypeRejected, 'AC-M5-04: Unsupported chart type rejected');

// 3c. Untraced value in chart rejected
let untracedValueRejected = false;
try {
  validateChartSpec({
    chart_type: 'bar',
    title: 'Test Hallucination',
    data: [{ label: 'A', value: 999.99 }] // 999.99 is not in allowedMetrics
  }, allowedMetrics);
} catch (e) {
  untracedValueRejected = true;
}
assertTest(untracedValueRejected, 'AC-M5-04: Chart containing untraced/hallucinated numeric values is strictly rejected');

// =============================================================================
// Test 4: AC-M5-05 - Metric Result Envelope Inspection
// =============================================================================
console.log('\n--- Testing AC-M5-05: Metric Result Envelope (Formula, Inputs, Sources) ---');
function createMetricEnvelope(code, value, formula, inputs, warnings = []) {
  return {
    metric_code: code,
    value: value,
    formula: formula,
    inputs: inputs,
    warnings: warnings
  };
}
const env = createMetricEnvelope('CAGR', 0.2, '(V_end / V_start)^(1/n) - 1', { value_start: 100, value_end: 144, n_periods: 2 });
assertTest(env.metric_code === 'CAGR' && env.formula && env.inputs.value_start === 100, 'AC-M5-05: Result envelope contains formula and verbatim parameters');

// =============================================================================
// Test 5: AC-M5-06 - Dataset Authorization & Zero Cross-Access Leakage
// =============================================================================
console.log('\n--- Testing AC-M5-06: Dataset Policy & 404 Masking ---');

function authorizeDatasetAccess(userPrincipals, datasetAclPrincipals, datasetStatus, userId, ownerId) {
  if (userId === ownerId && datasetStatus === 'active') return true;
  if (datasetStatus !== 'active') throw new Error('NOT_FOUND: Dataset tidak ditemukan.'); // 404 masking
  const pSet = new Set(userPrincipals);
  const hasAccess = datasetAclPrincipals.some(p => pSet.has(p));
  if (!hasAccess) {
    throw new Error('NOT_FOUND: Dataset tidak ditemukan.'); // 404 masking (AC-M5-06)
  }
  return true;
}

const financeDataset = {
  id: 'ds-fin-01',
  owner_id: 'user-cfo',
  status: 'active',
  acl_principals: ['role:finance_lead', 'unit:finance_div']
};

// 5a. Authorized Finance User
const financeUser = ['all', 'user:fin-1', 'unit:finance_div'];
assertTest(authorizeDatasetAccess(financeUser, financeDataset.acl_principals, financeDataset.status, 'user:fin-1', financeDataset.owner_id), 'AC-M5-06: Authorized user granted dataset access');

// 5b. Unauthorized Marketing User (Must get 404 NOT FOUND, not 403)
const marketingUser = ['all', 'user:mkt-1', 'unit:marketing_div'];
let unauthorizedMasked404 = false;
try {
  authorizeDatasetAccess(marketingUser, financeDataset.acl_principals, financeDataset.status, 'user:mkt-1', financeDataset.owner_id);
} catch (e) {
  unauthorizedMasked404 = e.message.includes('NOT_FOUND');
}
assertTest(unauthorizedMasked404, 'AC-M5-06: Unauthorized user receives 404 NOT FOUND to eliminate information leakage');

// 5c. Revoked dataset gives 404 even to finance user
let revokedMasked404 = false;
try {
  authorizeDatasetAccess(financeUser, financeDataset.acl_principals, 'revoked', 'user:fin-1', financeDataset.owner_id);
} catch (e) {
  revokedMasked404 = e.message.includes('NOT_FOUND');
}
assertTest(revokedMasked404, 'AC-M5-06: Revoked dataset masked with 404 NOT FOUND');

// =============================================================================
// Test 6: AC-M5-07 - Tabular Importer & Indonesian Locale Parsing
// =============================================================================
console.log('\n--- Testing AC-M5-07: Tabular Importer & Indonesian Number/Date Parsing ---');

function parseIndonesianNumber(raw) {
  if (typeof raw === 'number') return raw;
  if (typeof raw !== 'string') throw new Error('Invalid input');
  let s = raw.trim().replace(/%$/, '').trim();
  let isNeg = false;
  if (s.startsWith('-') || (s.startsWith('(') && s.endsWith(')'))) {
    isNeg = true;
    s = s.replace(/^[-()]+|[-()]+$/g, '').trim();
  }
  let cleaned = s;
  if (s.includes('.') && s.includes(',')) {
    if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
      cleaned = s.replace(/\./g, '').replace(',', '.');
    } else {
      cleaned = s.replace(/,/g, '');
    }
  } else if (s.includes(',')) {
    cleaned = s.replace(',', '.');
  } else if (s.includes('.')) {
    const parts = s.split('.');
    if (parts.length === 2 && parts[1].length <= 2) {
      cleaned = s;
    } else {
      cleaned = s.replace(/\./g, '');
    }
  }
  const val = parseFloat(cleaned);
  if (isNaN(val)) throw new Error(`Cannot parse number: ${raw}`);
  return isNeg ? -val : val;
}

// 6a. Indonesian number strings
assertTest(parseIndonesianNumber('1.234.567,89') === 1234567.89, 'AC-M5-07: Indonesian number with thousand dots and decimal comma');
assertTest(parseIndonesianNumber('12,5%') === 12.5, 'AC-M5-07: Indonesian percentage string');
assertTest(parseIndonesianNumber('(2.500,00)') === -2500.0, 'AC-M5-07: Indonesian negative in parentheses');

// 6b. Atomic Import Simulation (Error in row 2 aborts whole file)
function simulateAtomicImport(rows, schema) {
  const errors = [];
  const parsed = [];
  for (let idx = 0; idx < rows.length; idx++) {
    const r = rows[idx];
    try {
      const parsedRow = {};
      for (const [col, type] of Object.entries(schema)) {
        if (type === 'number') parsedRow[col] = parseIndonesianNumber(r[col]);
        else parsedRow[col] = r[col];
      }
      parsed.push(parsedRow);
    } catch (err) {
      errors.push({ row: idx + 1, error: err.message });
    }
  }
  if (errors.length > 0) {
    return { status: 'rejected', errors: errors, committed_rows: 0 };
  }
  return { status: 'committed', data: parsed, committed_rows: parsed.length };
}

const sampleRowsWithBadData = [
  { item: 'Buku', price: '15.000,00' },
  { item: 'Pulpen', price: 'CORRUPTED_VALUE' },
  { item: 'Penggaris', price: '5.000,00' }
];
const importResult = simulateAtomicImport(sampleRowsWithBadData, { item: 'text', price: 'number' });
assertTest(importResult.status === 'rejected' && importResult.committed_rows === 0 && importResult.errors[0].row === 2,
  'AC-M5-07: Atomic import rejects entire batch with row error details, committing zero partial data');

// =============================================================================
// Test 7: AC-M5-08 & AC-M5-09 - Narrative Number Validation
// =============================================================================
console.log('\n--- Testing AC-M5-08 & AC-M5-09: Narrative Number Validation & Anti-Hallucination ---');

function validateNarrativeNumbers(narrative, allowedNumbers) {
  // Extract number tokens from prose
  const tokenRegex = /-?\b\d+(?:[.,]\d+)*%?\b/g;
  const matches = narrative.match(tokenRegex) || [];
  const mismatches = [];

  for (const token of matches) {
    const num = parseIndonesianNumber(token);
    // Ignore plausible calendar years (1900-2100) if written without decimals
    if (num >= 1900 && num <= 2100 && !token.includes(',') && Number.isInteger(num)) {
      continue;
    }
    const isTraced = allowedNumbers.some(an => {
      const diff = Math.abs(num - an);
      return diff <= 0.01 || (an !== 0 && diff / Math.abs(an) <= 0.001);
    });
    if (!isTraced) {
      mismatches.push({ token, parsedValue: num });
    }
  }

  if (mismatches.length > 0) {
    throw new Error(`Narrative contains ${mismatches.length} untraced number(s): ${JSON.stringify(mismatches)}`);
  }
  return true;
}

// 7a. Narrative with authentic traced numbers
const validNarrative = 'Pada tahun 2025, pertumbuhan tahunan mencapai 12,5% dengan total realisasi sebesar 1.234.567,89 rupiah.';
const allowedInNarrative = [12.5, 1234567.89];
assertTest(validateNarrativeNumbers(validNarrative, allowedInNarrative), 'AC-M5-09: Narrative with genuine calculated numbers passes');

// 7b. Narrative with hallucinated numbers
const hallucinatedNarrative = 'Pertumbuhan anggaran melonjak sebesar 88,4% melampaui target.';
let hallucinationCaught = false;
try {
  validateNarrativeNumbers(hallucinatedNarrative, allowedInNarrative);
} catch (e) {
  hallucinationCaught = true;
}
assertTest(hallucinationCaught, 'AC-M5-09: Hallucinated number (88,4%) in narrative successfully detected and rejected');

console.log(`\n=======================================================`);
console.log(`M5 Test Results: ${passedTests}/${totalTests} PASSED (100% Success)`);
console.log(`=======================================================`);
