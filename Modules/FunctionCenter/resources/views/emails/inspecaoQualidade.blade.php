<!doctype html>
<html lang="pt-BR">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Inspeção de Qualidade</title>
	</head>
	<body style="margin:0; padding:0; background:#f4f5f7; font-family: Arial, sans-serif; color:#222;">
		<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f5f7; padding:24px 0;">
			<tr>
				<td align="center">
					<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
						<tr>
							<td style="background:#0f172a; color:#ffffff; padding:20px 24px; font-size:18px; font-weight:bold;">
								Inspeção de Qualidade
							</td>
						</tr>
						<tr>
							<td style="padding:20px 24px; font-size:14px; line-height:1.5;">
								<p style="margin:0 0 12px 0;">Olá,</p>
								<p style="margin:0 0 16px 0;">Segue abaixo as informações para inspeção.</p>

								@php
									$row = $mailData['body'] ?? [];
									$columns = [
										'Quantidade Recebida' => 'qtdRec',
										'SKU' => 'codPro',
										'Numero NFE' => 'numNfc',
										'Numero OCP' => 'numOcp',
										'Sequencia' => 'seqIpo',
										'Pallet' => 'codPal'
									];
									$rows = !empty($row) ? [$row] : [];
								@endphp


								@if (!empty($rows) && !empty($columns))
									<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; font-size:13px;">
										<thead>
											<tr>
												@foreach ($columns as $label => $key)
													<th align="left" style="border-bottom:1px solid #e5e7eb; padding:8px; background:#f8fafc;">
														{{ $label }}
													</th>
												@endforeach
											</tr>
										</thead>
										<tbody>
											@foreach ($rows as $row)
												<tr>
													@foreach ($columns as $label => $key)
														<td style="border-bottom:1px solid #eef2f7; padding:8px;">
															{{ $row[$key] ?? '' }}
														</td>
													@endforeach
												</tr>
											@endforeach
										</tbody>
									</table>
								@else
									<p style="margin:0; color:#64748b;">Sem dados para exibir.</p>
								@endif

								<p style="margin:16px 0 0 0; color:#64748b;">Mensagem automatica.</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
