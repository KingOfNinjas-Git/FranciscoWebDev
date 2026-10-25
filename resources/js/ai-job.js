// Shared by every admin screen that uses the AI (blog drafts, translations of posts and projects).
// The server answers a request at once and does the slow AI work afterwards; the browser then polls for the result.

/** A readable message from a failed request. */
export function errorMessage(e) {
    return e?.response?.data?.message
        || (e?.response?.data?.errors && Object.values(e.response.data.errors).flat()[0])
        || (e?.response?.status === 401 ? 'Your admin session expired. Log in again.' : null)
        || e?.message
        || 'Something went wrong.';
}

/**
 * Starts a job and waits for it.
 *
 * @param {object}   o
 * @param {object}   o.axios      the HTTP client
 * @param {string}   o.startUrl   POST here to start the job (answers { id })
 * @param {string}   o.statusUrl  GET {statusUrl}/{id} until the job is done or failed
 * @param {object}   o.payload    request body
 * @param {Function} [o.onTick]   called with the seconds waited so far, before every check
 * @returns the finished job ({ status: 'done', result, ... }); throws if it failed or took too long
 */
export async function runJob({ axios, startUrl, statusUrl, payload, onTick = () => {}, pollMs = 2000, retryMs = 5000, maxMs = 6 * 60 * 1000 }) {
    const start = await axios.post(startUrl, payload);
    const started = Date.now();
    const tooLong = () => new Error('The AI is taking too long. Please try again.');
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    for (;;) {
        onTick(Math.round((Date.now() - started) / 1000));
        await sleep(pollMs);

        let res;
        try {
            res = await axios.get(statusUrl + '/' + start.data.id);
        } catch (e) {
            // A busy moment (rate limit, brief network error) shouldn't lose a result that is still being written
            if (e?.response?.status === 429 || !e?.response) {
                if (Date.now() - started > maxMs) throw tooLong();
                await sleep(retryMs);
                continue;
            }
            throw e;
        }

        if (res.data.status === 'done') return res.data;
        if (res.data.status === 'failed') throw new Error(res.data.error || 'The AI request failed.');
        if (Date.now() - started > maxMs) throw tooLong();
    }
}
