export function getEffectiveRankName(source) {
  const s = source || {};
  return (
    s?.rank?.name ||
    s?.rank_name ||
    s?.computed_rank?.name ||
    s?.computedRank?.name ||
    s?.stats?.current_rank ||
    "—"
  );
}

