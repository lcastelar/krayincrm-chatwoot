<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krayin CRM - Chatwoot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #15803d; /* Rich Woofed-style green */
            --brand-green-active: #16a34a;
            --brand-green-hover: #dcfce7;
            --brand-red: #dc2626;
            --brand-red-active: #ef4444;
            --brand-red-hover: #fee2e2;
            --brand-blue: #2563eb;
            --brand-blue-dark: #1d4ed8;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body { 
            background-color: #ffffff; 
            color: var(--gray-800); 
            padding: 28px 32px; 
            font-size: 14px; 
            -webkit-font-smoothing: antialiased; 
        }

        /* Top Header */
        .contact-header {
            margin-bottom: 28px;
        }
        .contact-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .contact-full-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 8px;
        }
        .kanban-icon-btn {
            color: var(--gray-400);
            text-decoration: none;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .kanban-icon-btn:hover { 
            color: var(--gray-900); 
            background: var(--gray-100);
        }

        .account-title {
            font-size: 24px;
            font-weight: 700;
            color: #2e384d;
            margin-bottom: 18px;
            line-height: 1.2;
            letter-spacing: -0.4px;
        }

        .contact-badges-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .contact-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            text-decoration: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .contact-badge:hover {
            border-color: var(--gray-400);
            background: var(--gray-50);
        }
        .contact-badge svg {
            width: 15px;
            height: 15px;
            stroke: var(--gray-500);
        }

        /* Tags List */
        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 4px;
            margin-bottom: 28px;
        }
        .tag-pill {
            font-size: 12px;
            font-weight: 500;
            padding: 5px 13px;
            border-radius: 16px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            letter-spacing: 0.2px;
        }

        /* Section Box */
        .section-box {
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            background: #ffffff;
            overflow: visible;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: #ffffff;
            border-bottom: 1.5px solid var(--gray-200);
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-800);
        }
        .btn-new-deal {
            background: transparent;
            color: var(--gray-600);
            border: 1.5px solid var(--gray-200);
            font-size: 13px;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-new-deal:hover {
            background: var(--gray-100);
            color: var(--gray-900);
            border-color: var(--gray-300);
        }

        /* Deals List */
        .deal-row {
            padding: 22px 24px;
            border-bottom: 1.5px solid var(--gray-200);
            display: grid;
            grid-template-columns: 1fr 42px;
            gap: 18px;
            align-items: center;
            transition: background 0.15s;
            position: relative;
        }
        .deal-row:last-child {
            border-bottom: none;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .deal-row:hover {
            background: #fcfdfe;
        }

        .deal-info {
            overflow: visible;
            position: relative;
        }
        .deal-top-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 8px;
        }
        /* Deal Title: Clean, Regular weight (No Bold) */
        .deal-title {
            font-size: 15px;
            font-weight: 400; /* Regular weight */
            color: var(--gray-900);
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            letter-spacing: -0.1px;
            transition: color 0.15s;
        }
        .deal-title:hover {
            color: var(--brand-blue);
        }
        .deal-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--brand-green);
            white-space: nowrap;
        }
        .deal-value.lost {
            color: var(--brand-red);
        }

        .deal-meta-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 14px;
        }
        .meta-dot {
            font-size: 8px;
            color: var(--gray-300);
        }
        .pipeline-tag {
            background: #f8fafc;
            color: var(--gray-700);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid var(--gray-200);
        }

        /* Forward Arrow Chevron Progress Bar */
        .stage-bar {
            display: flex;
            height: 14px;
            width: 100%;
            position: relative;
            background: transparent;
            gap: 2px;
            overflow: visible;
        }

        .stage-bar-item {
            flex: 1;
            height: 100%;
            background: #e2e8f0; /* Default upcoming gray */
            cursor: pointer;
            position: relative;
            transition: background 0.15s;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%, 8px 50%);
        }

        /* First chevron (no indent on left) */
        .stage-bar-item:first-child {
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);
            border-top-left-radius: 3px;
            border-bottom-left-radius: 3px;
        }

        /* Last chevron (no point on right) */
        .stage-bar-item:last-child {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 8px 50%);
            border-top-right-radius: 3px;
            border-bottom-right-radius: 3px;
        }

        /* Green Mode (Default / Won) */
        .stage-bar-item.completed,
        .stage-bar-item.active {
            background: var(--brand-green);
        }
        .stage-bar-item:hover {
            opacity: 0.9;
        }
        .stage-bar:not(.lost-bar) .stage-bar-item:hover {
            background: #86efac;
        }

        /* Red Mode (Lost Stage) */
        .stage-bar.lost-bar .stage-bar-item.completed,
        .stage-bar.lost-bar .stage-bar-item.active,
        .stage-bar.lost-bar .stage-bar-item.lost {
            background: var(--brand-red);
        }
        .stage-bar.lost-bar .stage-bar-item:hover {
            background: #fca5a5;
        }

        /* Stage Tooltip Bubble */
        .stage-tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            color: #334155;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease, transform 0.15s ease;
            z-index: 100;
        }
        .stage-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: #ffffff transparent transparent transparent;
        }
        .stage-tooltip::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: var(--gray-200) transparent transparent transparent;
        }
        .stage-bar-item:hover .stage-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-2px);
        }

        /* Action Arrow Button */
        .deal-action-btn {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            background: #ffffff;
            color: var(--gray-600);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            align-self: center;
        }
        .deal-action-btn:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            color: var(--gray-900);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        .deal-action-btn svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
        }

        /* Top Iframe Bar */
        #iframe-nav-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border: 1.5px solid var(--gray-200);
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .btn-back {
            background: #ffffff;
            border: 1.5px solid var(--gray-300);
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            color: var(--gray-700);
            transition: background 0.15s;
        }
        .btn-back:hover {
            background: var(--gray-100);
        }

        /* Form Modal */
        .form-modal {
            background: #ffffff;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 9px 12px; border: 1.5px solid var(--gray-300); border-radius: 6px; font-size: 13.5px; }
        .form-control:focus { outline: none; border-color: var(--brand-blue); }

        .btn-submit {
            background: var(--brand-blue);
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-submit:hover { background: var(--brand-blue-dark); }

        #lead-full-iframe {
            width: 100%;
            height: calc(100vh - 75px);
            border: none;
            border-radius: 10px;
            background: #ffffff;
        }

        .loading-spinner { text-align: center; padding: 50px 10px; color: var(--gray-500); font-size: 14px; }
        .hidden { display: none !important; }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gray-900);
            color: #ffffff;
            padding: 9px 18px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

    <!-- Top Navigation Bar when inside Full Lead View -->
    <div id="iframe-nav-bar" class="hidden">
        <button class="btn-back" onclick="returnToSummary()">
            ← Voltar
        </button>
        <span style="font-weight: 600; font-size: 14px; color: var(--gray-900);" id="iframe-nav-title">Krayin CRM</span>
        <button class="btn-back" onclick="reloadIframe()">
            ↻ Atualizar
        </button>
    </div>

    <!-- Main Widget Container -->
    <div id="main-widget-container">
        <!-- State Loading -->
        <div id="state-loading" class="loading-spinner">
            ⌛ Carregando dados do CRM...
        </div>

        <!-- State Found (Contact in CRM) -->
        <div id="state-found" class="hidden">
            <!-- Contact Header -->
            <div class="contact-header">
                <div class="contact-top-row">
                    <span class="contact-full-name" id="contact-full-name">Nome do Contato</span>
                    <a href="{{ route('admin.leads.index') }}" target="_blank" rel="noopener noreferrer" onclick="window.open('{{ route('admin.leads.index') }}', '_blank'); return false;" class="kanban-icon-btn" title="Abrir Lista de Negócios no Krayin CRM">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="5" height="18" rx="1"/>
                            <rect x="10" y="3" width="5" height="12" rx="1"/>
                            <rect x="17" y="3" width="5" height="15" rx="1"/>
                        </svg>
                    </a>
                </div>

                <h1 class="account-title" id="account-title">{{ config('app.name', 'Krayin CRM') }}</h1>

                <div class="contact-badges-row">
                    <a href="#" id="contact-phone-link" class="contact-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <span id="contact-phone">+1 ...</span>
                    </a>

                    <a href="#" id="contact-email-link" class="contact-badge hidden">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <span id="contact-email">email@...</span>
                    </a>
                </div>

                <!-- Contact Tags -->
                <div class="tags-list hidden" id="contact-tags"></div>
            </div>

            <!-- Form Create New Lead -->
            <div id="form-new-lead" class="form-modal hidden">
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 12px; color: var(--gray-900);">Novo Negócio</div>
                <div class="form-group">
                    <label>Título do Negócio *</label>
                    <input type="text" id="new-lead-title" class="form-control" placeholder="Ex: Financiamento RTO">
                </div>
                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="number" id="new-lead-value" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Funil / Pipeline</label>
                    <select id="new-lead-pipeline" class="form-control" onchange="updateNewLeadStages()"></select>
                </div>
                <div class="form-group">
                    <label>Estágio Inicial</label>
                    <select id="new-lead-stage" class="form-control"></select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 14px;">
                    <button class="btn-submit" onclick="submitCreateLead()">Salvar Negócio</button>
                    <button class="btn-back" onclick="toggleNewLeadForm()">Cancelar</button>
                </div>
            </div>

            <!-- Deals Section Container -->
            <div class="section-box">
                <div class="section-header">
                    <span class="section-title">Negócios</span>
                    <button class="btn-new-deal" onclick="toggleNewLeadForm()">Novo negócio</button>
                </div>

                <div id="deals-list-container"></div>
            </div>
        </div>

        <!-- State Not Found -->
        <div id="state-not-found" class="form-modal hidden">
            <div style="font-weight: 700; font-size: 15px; color: #b91c1c; margin-bottom: 8px;">Contato não cadastrado</div>
            <p style="font-size: 13px; color: var(--gray-600); margin-bottom: 16px;">
                Deseja cadastrar este cliente e abrir o primeiro negócio no Krayin CRM?
            </p>

            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" id="nf-name" class="form-control">
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" id="nf-phone" class="form-control">
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" id="nf-email" class="form-control">
            </div>
            <div class="form-group">
                <label>Título do Negócio *</label>
                <input type="text" id="nf-lead-title" class="form-control" value="Oportunidade Chatwoot">
            </div>
            <div class="form-group">
                <label>Funil</label>
                <select id="nf-pipeline" class="form-control" onchange="updateNfStages()"></select>
            </div>
            <div class="form-group">
                <label>Estágio Inicial</label>
                <select id="nf-stage" class="form-control"></select>
            </div>

            <button class="btn-submit" style="width: 100%; margin-top: 6px; padding: 10px;" onclick="submitCreateContactAndLead()">
                Cadastrar no Krayin CRM
            </button>
        </div>
    </div>

    <!-- Full Lead Iframe Container -->
    <iframe id="lead-full-iframe" class="hidden" src="about:blank"></iframe>

    <!-- Toast Notification -->
    <div id="toast" class="toast">Mensagem</div>

    <script>
        let currentContact = null;
        let globalData = null;

        // Listen to Chatwoot appContext postMessage
        window.addEventListener('message', function(event) {
            if (typeof event.data === 'string' && event.data.includes('appContext')) {
                try {
                    const parsed = JSON.parse(event.data);
                    if (parsed.event === 'appContext' && parsed.data.contact) {
                        currentContact = parsed.data.contact;
                        searchContact(currentContact);
                    }
                } catch (e) {
                    console.error('Error parsing appContext message:', e);
                }
            }
        });

        // Request appContext from parent Chatwoot window on load
        window.parent.postMessage('chatwoot-dashboard-app:fetch-info', '*');

        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.innerText = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2000);
        }

        function searchContact(contact) {
            fetch('/chatwoot/embed/search', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contact: contact })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('state-loading').classList.add('hidden');
                globalData = data;

                if (data.found) {
                    renderFoundState(data);
                } else {
                    renderNotFoundState(contact, data.pipelines);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('state-loading').innerText = '❌ Erro de conexão com o Krayin CRM.';
            });
        }

        function renderFoundState(data) {
            document.getElementById('state-found').classList.remove('hidden');
            document.getElementById('state-not-found').classList.add('hidden');

            const p = data.person;
            document.getElementById('contact-full-name').innerText = p.name || 'Contato';
            document.getElementById('account-title').innerText = "D'Mar Electronics";

            const phoneVal = (p.contact_numbers && p.contact_numbers[0]) ? p.contact_numbers[0].value : (currentContact.phone_number || '');
            const emailVal = (p.emails && p.emails[0]) ? p.emails[0].value : (currentContact.email || '');

            if (phoneVal) {
                document.getElementById('contact-phone').innerText = phoneVal;
                document.getElementById('contact-phone-link').href = `tel:${phoneVal}`;
                document.getElementById('contact-phone-link').classList.remove('hidden');
            } else {
                document.getElementById('contact-phone-link').classList.add('hidden');
            }

            if (emailVal) {
                document.getElementById('contact-email').innerText = emailVal;
                document.getElementById('contact-email-link').href = `mailto:${emailVal}`;
                document.getElementById('contact-email-link').classList.remove('hidden');
            } else {
                document.getElementById('contact-email-link').classList.add('hidden');
            }

            // Render Tags
            const tagsContainer = document.getElementById('contact-tags');
            tagsContainer.innerHTML = '';
            if (p.tags && p.tags.length > 0) {
                p.tags.forEach(t => {
                    const span = document.createElement('span');
                    span.className = 'tag-pill';
                    span.innerText = t;
                    tagsContainer.appendChild(span);
                });
                tagsContainer.classList.remove('hidden');
            } else {
                tagsContainer.classList.add('hidden');
            }

            renderDealsList(data.leads);
            populatePipelines('new-lead-pipeline', 'new-lead-stage', data.pipelines);
        }

        function renderDealsList(leads) {
            const container = document.getElementById('deals-list-container');
            container.innerHTML = '';

            if (!leads || leads.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 32px; color: var(--gray-400); font-size: 13px;">
                        Nenhum negócio ativo.
                    </div>
                `;
                return;
            }

            leads.forEach(lead => {
                const row = document.createElement('div');
                row.className = 'deal-row';

                let isLost = lead.is_lost || lead.stage_code === 'lost';
                let barClass = isLost ? 'stage-bar lost-bar' : 'stage-bar';

                let stagesHtml = '';
                if (lead.stages && lead.stages.length > 0) {
                    let currentStageIndex = lead.stages.findIndex(s => s.id === lead.stage_id);
                    if (currentStageIndex === -1) currentStageIndex = 0;

                    stagesHtml = `
                        <div class="${barClass}">
                            ${lead.stages.map((s, idx) => {
                                let cls = 'stage-bar-item';
                                if (s.id === lead.stage_id) {
                                    if (s.code === 'lost' || isLost) cls += ' lost active';
                                    else if (s.code === 'won') cls += ' won active';
                                    else cls += ' active';
                                } else if (idx <= currentStageIndex) {
                                    cls += ' completed';
                                }
                                return `
                                    <div class="${cls}" onclick="changeLeadStage(${lead.id}, ${s.id}, '${escapeHtml(s.name)}')">
                                        <div class="stage-tooltip">${escapeHtml(s.name)}</div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                }

                row.innerHTML = `
                    <div class="deal-info">
                        <div class="deal-top-line">
                            <a href="javascript:void(0)" onclick="openFullLeadView(${lead.id}, '${escapeHtml(lead.title)}')" class="deal-title" title="Ver detalhes do negócio">
                                ${escapeHtml(lead.title)}
                            </a>
                            <span class="deal-value ${isLost ? 'lost' : ''}">R$ ${lead.lead_value_formatted}</span>
                        </div>
                        <div class="deal-meta-line">
                            <span class="pipeline-tag">${escapeHtml(lead.pipeline_name)}</span>
                            <span class="meta-dot">•</span>
                            <span>${lead.created_at}</span>
                            <span class="meta-dot">•</span>
                            <span>Etapa: <strong style="color: ${isLost ? '#dc2626' : '#334155'}">${escapeHtml(lead.stage_name)}</strong></span>
                        </div>
                        ${stagesHtml}
                    </div>

                    <a href="javascript:void(0)" onclick="openFullLeadView(${lead.id}, '${escapeHtml(lead.title)}')" class="deal-action-btn" title="Abrir página completa do negócio">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"/>
                            <polyline points="7 7 17 7 17 17"/>
                        </svg>
                    </a>
                `;

                container.appendChild(row);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function changeLeadStage(leadId, stageId, stageName) {
            fetch('/chatwoot/embed/lead/update-stage', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lead_id: leadId, stage_id: stageId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Etapa: ${stageName}`);
                    searchContact(currentContact);
                }
            });
        }

        function openFullLeadView(leadId, leadTitle) {
            const iframe = document.getElementById('lead-full-iframe');
            const mainWidget = document.getElementById('main-widget-container');
            const navBar = document.getElementById('iframe-nav-bar');
            const navTitle = document.getElementById('iframe-nav-title');

            navTitle.innerText = leadTitle || `Lead #${leadId}`;
            // Pass ?embedded=1 so Krayin hides its top navbar, sidebar and footer inside the iframe
            iframe.src = `{{ url('/admin/leads/view') }}/${leadId}?embedded=1`;

            mainWidget.classList.add('hidden');
            navBar.classList.remove('hidden');
            iframe.classList.remove('hidden');
        }

        function returnToSummary() {
            const iframe = document.getElementById('lead-full-iframe');
            const mainWidget = document.getElementById('main-widget-container');
            const navBar = document.getElementById('iframe-nav-bar');

            iframe.src = 'about:blank';
            iframe.classList.add('hidden');
            navBar.classList.add('hidden');
            mainWidget.classList.remove('hidden');

            searchContact(currentContact);
        }

        function reloadIframe() {
            const iframe = document.getElementById('lead-full-iframe');
            iframe.src = iframe.src;
        }

        function toggleNewLeadForm() {
            document.getElementById('form-new-lead').classList.toggle('hidden');
        }

        function populatePipelines(pipelineElId, stageElId, pipelines) {
            const pEl = document.getElementById(pipelineElId);
            pEl.innerHTML = pipelines.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
            updateStagesFor(pipelineElId, stageElId, pipelines);
        }

        function updateStagesFor(pipelineElId, stageElId, pipelines) {
            const pId = parseInt(document.getElementById(pipelineElId).value);
            const pipeline = (pipelines || globalData.pipelines).find(p => p.id === pId);
            const sEl = document.getElementById(stageElId);
            if (pipeline && pipeline.stages) {
                sEl.innerHTML = pipeline.stages.map(s => `<option value="${s.id}">${escapeHtml(s.name)}</option>`).join('');
            }
        }

        function updateNewLeadStages() {
            updateStagesFor('new-lead-pipeline', 'new-lead-stage', globalData.pipelines);
        }

        function updateNfStages() {
            updateStagesFor('nf-pipeline', 'nf-stage', globalData.pipelines);
        }

        function submitCreateLead() {
            const title = document.getElementById('new-lead-title').value.trim();
            if (!title) {
                alert('Por favor, informe o título do negócio.');
                return;
            }

            const payload = {
                person_id: globalData.person.id,
                title: title,
                lead_value: document.getElementById('new-lead-value').value || 0,
                pipeline_id: document.getElementById('new-lead-pipeline').value,
                stage_id: document.getElementById('new-lead-stage').value,
            };

            fetch('/chatwoot/embed/lead/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Negócio criado!');
                    toggleNewLeadForm();
                    document.getElementById('new-lead-title').value = '';
                    document.getElementById('new-lead-value').value = '';
                    searchContact(currentContact);
                } else {
                    alert(data.message || 'Erro ao criar negócio');
                }
            });
        }

        function renderNotFoundState(contact, pipelines) {
            document.getElementById('state-not-found').classList.remove('hidden');
            document.getElementById('state-found').classList.add('hidden');

            document.getElementById('nf-name').value = contact.name || '';
            document.getElementById('nf-email').value = contact.email || '';
            document.getElementById('nf-phone').value = contact.phone_number || '';

            populatePipelines('nf-pipeline', 'nf-stage', pipelines);
        }

        function submitCreateContactAndLead() {
            const name = document.getElementById('nf-name').value.trim();
            const title = document.getElementById('nf-lead-title').value.trim();

            if (!name || !title) {
                alert('Nome e Título do negócio são obrigatórios.');
                return;
            }

            const payload = {
                name: name,
                email: document.getElementById('nf-email').value.trim(),
                phone: document.getElementById('nf-phone').value.trim(),
                title: title,
                lead_value: 0,
                pipeline_id: document.getElementById('nf-pipeline').value,
                stage_id: document.getElementById('nf-stage').value,
            };

            fetch('/chatwoot/embed/lead/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Contato e Negócio criados!');
                    searchContact(currentContact);
                } else {
                    alert(data.message || 'Erro ao cadastrar contato');
                }
            });
        }
    </script>
</body>
</html>
