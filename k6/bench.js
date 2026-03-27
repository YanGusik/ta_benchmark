import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8083';

export const options = {
    scenarios: {
        bench: {
            executor: 'constant-arrival-rate',
            rate: 1000,
            timeUnit: '1s',
            duration: '30s',
            preAllocatedVUs: 100,
            maxVUs: 1000,
            gracefulStop: '2s',
            exec: 'bench',
        },
    },
    thresholds: {
        http_req_failed:   ['rate<0.01'],
        http_req_duration: ['p(95)<500'],
    },
};

export function bench() {
    const r = http.get(`${BASE_URL}/bench`);
    check(r, { 'bench 200': (r) => r.status === 200 });
    if (r.status !== 200) {
        console.log(`bench FAIL: status=${r.status} body=${r.body?.substring(0, 200)}`);
    }
}
