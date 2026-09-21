#!/usr/bin/env node
/**
 * Discord Polling Bot for Newpack Rolling Coverage
 * 
 * Polls WordPress REST API for new rolling coverage entries,
 * newsletter publishes, and content events, then posts to Discord channels.
 * 
 * Environment variables:
 *   DISCORD_WEBHOOK_URL    - Default Discord webhook URL
 *   WP_SITE_URL           - WordPress site URL (e.g. https://yoursite.com)
 *   WP_API_KEY            - API key for authentication (set in WP plugin)
 *   POLL_INTERVAL_MS      - Polling interval in ms (default: 30000 = 30s)
 *   STATE_FILE            - Path to state file (default: ./bot-state.json)
 */

const fs = require('fs');
const path = require('path');

// ─── Config ───
const DISCORD_WEBHOOK_URL = process.env.DISCORD_WEBHOOK_URL;
const WP_SITE_URL = process.env.WP_SITE_URL;
const WP_API_KEY = process.env.WP_API_KEY;
const POLL_INTERVAL_MS = parseInt(process.env.POLL_INTERVAL_MS || '30000', 10);
const STATE_FILE = process.env.STATE_FILE || path.join(__dirname, 'bot-state.json');

if (!DISCORD_WEBHOOK_URL || !WP_SITE_URL || !WP_API_KEY) {
  console.error('Missing required env vars: DISCORD_WEBHOOK_URL, WP_SITE_URL, WP_API_KEY');
  process.exit(1);
}

// ─── State Management ───
function loadState() {
  try {
    return JSON.parse(fs.readFileSync(STATE_FILE, 'utf8'));
  } catch {
    return { lastPoll: null, seenIds: {} };
  }
}

function saveState(state) {
  fs.writeFileSync(STATE_FILE, JSON.stringify(state, null, 2));
}

// ─── WordPress API ───
async function wpFetch(endpoint) {
  const url = `${WP_SITE_URL}/wp-json/newspack-discord-bot/v1${endpoint}`;
  const resp = await fetch(url, {
    headers: {
      'X-API-Key': WP_API_KEY,
      'Content-Type': 'application/json',
    },
  });
  if (!resp.ok) {
    throw new Error(`WP API error: ${resp.status} ${resp.statusText}`);
  }
  return resp.json();
}

// ─── Discord Webhook ───
async function postToDiscord(embed, webhookUrl) {
  const url = webhookUrl || DISCORD_WEBHOOK_URL;
  const resp = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      username: 'Newsroom Bot',
      embeds: [embed],
    }),
  });
  if (!resp.ok) {
    const body = await resp.text();
    throw new Error(`Discord webhook error: ${resp.status} ${body}`);
  }
}

// ─── Embed Builders ───
function buildCoverageEmbed(entry) {
  return {
    title: entry.title || 'New Coverage Entry',
    description: entry.content ? entry.content.substring(0, 2000) : '',
    url: entry.link || '',
    color: 0x1a73e8, // Blue
    fields: [
      { name: 'Coverage', value: entry.coverage_name || 'General', inline: true },
      { name: 'Author', value: entry.author || 'Unknown', inline: true },
      { name: 'Source', value: entry.source || 'Staff', inline: true },
    ].filter(f => f.value),
    timestamp: entry.date || new Date().toISOString(),
    footer: { text: 'Rolling Coverage' },
  };
}

function buildNewsletterEmbed(entry) {
  return {
    title: entry.title || 'Newsletter Published',
    description: entry.excerpt || '',
    url: entry.link || '',
    color: 0x34a853, // Green
    fields: [
      { name: 'Subscribers', value: String(entry.subscriber_count || 'N/A'), inline: true },
      { name: 'List', value: entry.list_name || 'All', inline: true },
    ],
    timestamp: entry.date || new Date().toISOString(),
    footer: { text: 'Newsletter' },
  };
}

function buildEventEmbed(entry) {
  return {
    title: entry.title || 'Newsroom Event',
    description: entry.description || '',
    url: entry.link || '',
    color: 0xfbbc04, // Yellow
    timestamp: entry.date || new Date().toISOString(),
    footer: { text: entry.event_type || 'Event' },
  };
}

// ─── Polling ───
async function poll() {
  const state = loadState();
  const since = state.lastPoll || new Date(Date.now() - 86400000).toISOString();

  try {
    // Poll for new coverage entries
    const coverages = await wpFetch(`/entries?since=${encodeURIComponent(since)}`);
    for (const entry of coverages.entries || []) {
      const key = `coverage-${entry.id}`;
      if (!state.seenIds[key]) {
        state.seenIds[key] = true;
        try {
          await postToDiscord(buildCoverageEmbed(entry));
          console.log(`Posted coverage entry: ${entry.id} - ${entry.title}`);
        } catch (err) {
          console.error(`Failed to post coverage ${entry.id}:`, err.message);
        }
      }
    }

    // Poll for new newsletters
    const newsletters = await wpFetch(`/newsletters?since=${encodeURIComponent(since)}`);
    for (const entry of newsletters.newsletters || []) {
      const key = `newsletter-${entry.id}`;
      if (!state.seenIds[key]) {
        state.seenIds[key] = true;
        try {
          await postToDiscord(buildNewsletterEmbed(entry));
          console.log(`Posted newsletter: ${entry.id} - ${entry.title}`);
        } catch (err) {
          console.error(`Failed to post newsletter ${entry.id}:`, err.message);
        }
      }
    }

    // Poll for events
    const events = await wpFetch(`/events?since=${encodeURIComponent(since)}`);
    for (const entry of events.events || []) {
      const key = `event-${entry.id}`;
      if (!state.seenIds[key]) {
        state.seenIds[key] = true;
        try {
          await postToDiscord(buildEventEmbed(entry));
          console.log(`Posted event: ${entry.id} - ${entry.title}`);
        } catch (err) {
          console.error(`Failed to post event ${entry.id}:`, err.message);
        }
      }
    }

    state.lastPoll = new Date().toISOString();
    saveState(state);
  } catch (err) {
    console.error('Poll error:', err.message);
  }
}

// ─── Main Loop ───
console.log(`Discord Polling Bot starting...`);
console.log(`  WordPress: ${WP_SITE_URL}`);
console.log(`  Interval:  ${POLL_INTERVAL_MS}ms`);
console.log(`  State:     ${STATE_FILE}`);

// Run immediately, then on interval
poll();
setInterval(poll, POLL_INTERVAL_MS);

// Graceful shutdown
process.on('SIGINT', () => {
  console.log('\nShutting down...');
  process.exit(0);
});
process.on('SIGTERM', () => {
  console.log('\nShutting down...');
  process.exit(0);
});

