import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8085';

export const options = {
    scenarios: {
        hello: {
            executor: 'constant-arrival-rate',
            rate: 10000,
            timeUnit: '1s',
            duration: '30s',
            preAllocatedVUs: 200,
            maxVUs: 2000,
            gracefulStop: '2s',
            exec: 'hello',
        },
    },
    thresholds: {
        http_req_failed:   ['rate<0.01'],
        http_req_duration: ['p(95)<100'],
    },
};

export function hello() {
    const r = http.get(`${BASE_URL}/hello`);
    check(r, { 'status 200': (r) => r.status === 200 });
}
