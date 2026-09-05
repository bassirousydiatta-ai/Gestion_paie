const STYLES_GRAVITE = {
  critique: 'bg-red-50 border-red-200 text-red-700',
  avertissement: 'bg-amber-50 border-amber-200 text-amber-700',
  info: 'bg-blue-50 border-blue-200 text-blue-700',
};

const ICONES_GRAVITE = {
  critique: '⛔',
  avertissement: '⚠️',
  info: 'ℹ️',
};

export default function AnomaliesAlert({ anomalies }) {
  if (!anomalies || anomalies.length === 0) return null;

  return (
    <div className="space-y-2 mb-4">
      {anomalies.map((anomalie) => (
        <div
          key={anomalie.code}
          className={`border rounded-lg px-4 py-2.5 text-sm flex gap-2 ${STYLES_GRAVITE[anomalie.gravite] || STYLES_GRAVITE.info}`}
        >
          <span>{ICONES_GRAVITE[anomalie.gravite] || 'ℹ️'}</span>
          <div>
            <p>{anomalie.message}</p>
            {anomalie.suggestion && <p className="text-xs opacity-80 mt-0.5">{anomalie.suggestion}</p>}
          </div>
        </div>
      ))}
    </div>
  );
}
